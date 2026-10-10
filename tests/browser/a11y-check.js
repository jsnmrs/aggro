/**
 * Browser-driven accessibility checks.
 *
 * Runs on the host against a live site (the DDEV container has no browser)
 * with `npm run browser-check`. BASE_URL overrides the target site.
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
  }

  await browser.close();

  const failures = results.filter((result) => !result.ok);
  for (const failure of failures) {
    console.log(`FAIL [${failure.page}] ${failure.check}: ${failure.detail}`);
  }
  console.log(`${results.length} checks, ${failures.length} failures`);
  process.exit(failures.length > 0 ? 1 : 0);
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
