/**
 * Browser-driven accessibility checks.
 *
 * Covers what the crawler scan cannot see. On every public page:
 * - the response status is what the route promises
 * - every <time datetime> value is ISO 8601
 * - nothing scrolls horizontally at a 320px wide viewport (WCAG 1.4.10)
 * - the skip link is the first Tab stop and lands on main#content
 * - every Tab stop shows a solid outline (WCAG 2.4.7)
 *
 * Runs on the host against a live site (the DDEV container has no browser)
 * with `npm run browser-check`. BASE_URL overrides the target site, and
 * VERBOSE=1 prints passing checks as well as failures.
 */

const { chromium } = require("playwright");

const BASE_URL = process.env.BASE_URL || "https://aggro.ddev.site";
const SITE_HOST = new URL(BASE_URL).host;
const NOT_FOUND_PATH = "/does-not-exist";
const DESKTOP = { width: 1280, height: 900 };

const results = [];

function record(page, check, ok, detail = "") {
  results.push({ page, check, ok, detail });
}

/**
 * Navigate to a path at the given viewport and strip the development
 * debug toolbar, which only exists when the site runs in development mode.
 */
async function open(page, path, viewport = DESKTOP) {
  await page.setViewportSize(viewport);
  const response = await page.goto(BASE_URL + path, { waitUntil: "load" });
  await page.evaluate(() => {
    document
      .querySelectorAll("#debug-bar, #debug-icon, .kint-rich")
      .forEach((node) => node.remove());
  });
  return response;
}

/**
 * Return the first link on a list page whose href matches the pattern.
 */
async function discover(page, path, pattern) {
  await open(page, path);
  const href = await page.evaluate((source) => {
    const regex = new RegExp(source);
    const link = [...document.querySelectorAll("a[href]")].find((a) =>
      regex.test(a.getAttribute("href")),
    );
    return link ? link.getAttribute("href") : null;
  }, pattern.source);
  if (!href) {
    throw new Error(`No link matching ${pattern} found on ${path}`);
  }
  return href;
}

async function checkStatus(page, name, path) {
  const response = await open(page, path);
  const expected = path === NOT_FOUND_PATH ? 404 : 200;
  const status = response ? response.status() : 0;
  record(
    name,
    "status",
    status === expected,
    `expected ${expected}, got ${status}`,
  );
}

/**
 * Every <time> carries a datetime in ISO 8601 form, as the timeAgo() view
 * helper writes it. Runs on the page checkStatus() left open.
 */
async function checkTimes(page, name) {
  const invalid = await page.evaluate(() => {
    const iso = /^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z))?$/;
    return [...document.querySelectorAll("time")]
      .map((time) => time.getAttribute("datetime"))
      .filter((value) => value === null || !iso.test(value))
      .map(String);
  });
  record(
    name,
    "time datetime",
    invalid.length === 0,
    `${invalid.length} invalid: ${invalid.slice(0, 5).join(", ")}`,
  );
}

/**
 * WCAG 1.4.10 Reflow: no horizontal scrolling at a 320px wide viewport.
 */
async function checkReflow(page, name, path) {
  await open(page, path, { width: 320, height: 640 });
  const reflow = await page.evaluate(() => {
    const root = document.scrollingElement;
    const overflowing = [...document.querySelectorAll("body *")]
      .filter((element) => {
        const box = element.getBoundingClientRect();
        return box.width > 0 && box.right > innerWidth + 1;
      })
      .slice(0, 5)
      .map((element) => {
        const box = element.getBoundingClientRect();
        const className = String(element.className).slice(0, 30);
        return `${element.tagName.toLowerCase()}.${className} right=${Math.round(box.right)}`;
      });
    return {
      scrollWidth: root.scrollWidth,
      clientWidth: root.clientWidth,
      overflowing,
    };
  });
  record(
    name,
    "reflow 320",
    reflow.scrollWidth <= reflow.clientWidth,
    `scrollWidth ${reflow.scrollWidth}, clientWidth ${reflow.clientWidth}${reflow.overflowing.length ? ": " + reflow.overflowing.join("; ") : ""}`,
  );
}

/**
 * Describe the focused element for failure messages.
 */
function describeActiveElement() {
  const element = document.activeElement;
  const text = (element.textContent || "").replace(/\s+/g, " ").trim();
  return `${element.tagName.toLowerCase()}${element.id ? "#" + element.id : ""} "${text.slice(0, 40)}"`;
}

/**
 * The skip link is the first Tab stop, activating it moves focus to
 * main#content, and the next Tab lands inside main.
 */
async function checkSkipLink(page, name, path) {
  await open(page, path);
  await page.keyboard.press("Tab");
  const stages = [
    [
      "first Tab stop is the skip link",
      await page.evaluate(() =>
        document.activeElement.classList.contains("skip"),
      ),
    ],
  ];
  await page.keyboard.press("Enter");
  await page.waitForTimeout(200);
  stages.push([
    "Enter moves focus to main#content",
    await page.evaluate(
      () => document.activeElement === document.querySelector("main#content"),
    ),
  ]);
  await page.keyboard.press("Tab");
  stages.push([
    "next Tab lands inside main",
    await page.evaluate(() =>
      document.querySelector("main#content").contains(document.activeElement),
    ),
  ]);
  const failed = stages.find(([, ok]) => !ok);
  const focused = await page.evaluate(describeActiveElement);
  record(
    name,
    "skip link",
    !failed,
    failed ? `${failed[0]} (focus is on ${focused})` : "",
  );
}

/**
 * WCAG 2.4.7 Focus Visible: every Tab stop shows a solid outline. Embeds
 * are taken out of the tab order first so the walk stays in this document.
 */
async function checkFocusOutlines(page, name, path) {
  await open(page, path);
  await page.evaluate(() => {
    document.querySelectorAll("iframe").forEach((frame) => {
      frame.tabIndex = -1;
    });
  });
  const offenders = [];
  let visited = 0;
  for (let press = 0; press < 300; press++) {
    await page.keyboard.press("Tab");
    const focused = await page.evaluate(() => {
      const element = document.activeElement;
      if (!element || element === document.body || element.dataset.a11ySeen) {
        return null;
      }
      element.dataset.a11ySeen = "1";
      const style = getComputedStyle(element);
      const text = (element.textContent || "").replace(/\s+/g, " ").trim();
      return {
        label: `${element.tagName.toLowerCase()} "${text.slice(0, 40)}"`,
        outlineStyle: style.outlineStyle,
        outlineWidth: parseFloat(style.outlineWidth),
      };
    });
    if (!focused) {
      break;
    }
    visited++;
    if (focused.outlineStyle !== "solid" || focused.outlineWidth < 1) {
      offenders.push(
        `${focused.label} (${focused.outlineStyle} ${focused.outlineWidth}px)`,
      );
    }
  }
  record(
    name,
    "focus outline",
    visited > 0 && offenders.length === 0,
    visited === 0
      ? "nothing received focus"
      : `${offenders.length} of ${visited} without a solid outline: ${offenders.slice(0, 5).join("; ")}`,
  );
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: DESKTOP,
  });
  // Keep embeds and trackers out so the checks only see the site itself.
  await context.route("**/*", (route) => {
    const host = new URL(route.request().url()).host;
    return host === SITE_HOST ? route.continue() : route.abort();
  });
  const page = await context.newPage();

  const videoPath = await discover(page, "/video", /^\/video\/[A-Za-z0-9_-]+$/);
  const sitePath = await discover(page, "/sites", /^\/sites\/[a-z0-9-]+$/);

  const pages = [
    ["home", "/"],
    ["stream", "/stream"],
    ["video list", "/video"],
    ["video list page 2", "/video/recent/2"],
    ["video detail", videoPath],
    ["directory", "/sites"],
    ["site detail", sitePath],
    ["about", "/about"],
    ["not found", NOT_FOUND_PATH],
  ];

  for (const [name, path] of pages) {
    await checkStatus(page, name, path);
    await checkTimes(page, name);
    await checkReflow(page, name, path);
    await checkSkipLink(page, name, path);
    await checkFocusOutlines(page, name, path);
  }

  await browser.close();

  for (const result of results) {
    if (!result.ok) {
      console.log(`FAIL [${result.page}] ${result.check}: ${result.detail}`);
    } else if (process.env.VERBOSE) {
      console.log(`ok   [${result.page}] ${result.check}: ${result.detail}`);
    }
  }
  const failures = results.filter((result) => !result.ok);
  console.log(`${results.length} checks, ${failures.length} failures`);
  process.exit(failures.length > 0 ? 1 : 0);
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
