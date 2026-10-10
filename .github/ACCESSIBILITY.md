# Accessibility statement for BMXfeed

[BMXfeed](https://bmxfeed.com) conforms to the Web Content Accessibility Guidelines (WCAG) 2.2 at Level AA. The site runs on the open-source aggro codebase and is built and maintained by Jason Morris. This statement says what the claim covers, how the site is tested, and how to report a problem.

The most recent evaluation was completed on October 10, 2026.

## Scope

This statement covers the public BMXfeed website:

- Featured page (`/`)
- Stream (`/stream`)
- Video list and video pages (`/video`, `/video/...`)
- Directory and site pages (`/sites`, `/sites/...`)
- About page (`/about`)
- The “page not found” (404) page

It doesn’t cover the admin interface (`/aggro/*`), the XML feeds (`/feed`, `/rss`, `/opml`, `/sitemap.xml`), or content controlled by other organizations. See “Third-party content” below.

## Conformance status

BMXfeed fully conforms to WCAG 2.2 Level AA. “Fully conforms” means the content meets the standard without exceptions. Section 508 of the Rehabilitation Act incorporates WCAG 2.0 Level AA, so meeting WCAG 2.2 Level AA also satisfies its requirements for web content.

The claim rests on a self-evaluation completed on October 10, 2026. It combined a review of the templates, view helpers, and stylesheets with a scripted browser review of 9 live pages. Every finding from that review was fixed in pull requests [#844](https://github.com/jsnmrs/aggro/pull/844), [#845](https://github.com/jsnmrs/aggro/pull/845), [#848](https://github.com/jsnmrs/aggro/pull/848), and [#850](https://github.com/jsnmrs/aggro/pull/850).

## What you can expect

- A “Skip to content” link is the first Tab stop on every page. It moves focus straight to the main content.
- Every focusable element shows a solid 3px outline. Keyboard order follows the reading order, and nothing traps focus.
- Landmarks are named (banner, Primary navigation, main, Footer navigation, and Pagination on list pages), and each page has one h1. The current page is marked in both navigation menus with bold text, a thicker underline, and a darker color, so the cue survives color blindness and forced-colors mode.
- Text contrast is at least 7.4 to 1 everywhere, well above the 4.5 to 1 minimum. Links are underlined, not marked by color alone.
- The layout reflows at a 320px wide viewport (400 percent zoom) with no horizontal scrolling. It also holds up under WCAG’s text-spacing override.
- The only motion is a view transition between pages, and it runs only when your system doesn’t ask for reduced motion.
- There is no front-end JavaScript. Pages are plain HTML and CSS, so they work with any browser and assistive technology combination.

Page titles put the specific part first (for example “Bloom | Directory | BMXfeed”). Dates carry machine-readable `datetime` values with a time zone. Videos and posts that arrive without a title get a readable fallback name that includes the source, so no link is ever empty.

## How we test

Accessibility checks run as part of the standard build, not only at audit time:

- An axe-based crawler ([accessibility-scan-action](https://github.com/double-great/accessibility-scan-action)) scans every reachable page of a DDEV build of the site. It runs on pull requests that touch views, helpers, CSS, or browser tests, every Thursday, and on demand. The report is uploaded as a workflow artifact and summarized as a pull request comment.
- A Playwright script drives headless Chromium through 9 pages, including the 404 page the crawler can’t reach. It confirms the skip link lands on the main content, every Tab stop shows a solid outline, nothing scrolls horizontally at 320px, and every `<time>` element carries a valid ISO 8601 `datetime`. It runs in CI after the crawl and locally with `npm run browser-check`.
- Stylelint with the `@double-great/stylelint-a11y` plugin checks every CSS change for focus styles, readable font sizes, justified text, and obsolete elements and attributes.
- PHPUnit view tests assert accessible markup directly. They cover page titles, iframe titles, fallback names for untitled videos and posts, list semantics on the video grid, the Vimeo embed’s attributes, the “Watch on YouTube” and “Watch on Vimeo” links, featured headings that hold only the site name, and screen reader labels on site and feed links.

The October 2026 review also covered what scripts can’t judge: contrast and use of color, link purpose, keyboard flow, focus visibility, target size, reflow, text spacing, forced-colors mode, reduced motion, and the sound of headings and links in VoiceOver on Safari and NVDA on Firefox.

## Third-party content

Some of what you see on BMXfeed comes from other organizations. These items are outside BMXfeed’s control and aren’t counted as failures:

- Videos play in embedded YouTube and Vimeo players. Their controls, caption support, and keyboard behavior belong to those platforms, and captions exist only when the uploader provides them. Every video page links directly to the video on YouTube or Vimeo so you can reach captions and the full description, even if the embed is blocked.
- The CI crawl blocks the player hosts so it measures only BMXfeed’s own markup.
- Post and video titles come from third-party RSS feeds and APIs as published. BMXfeed escapes them and supplies fallback names for empty titles, but it can’t correct the source’s wording.
- The stream, directory, and video pages link out to other websites. BMXfeed doesn’t control the accessibility of those destinations.

## Report a problem

If you hit a barrier on BMXfeed, [open an issue on GitHub](https://github.com/jsnmrs/aggro/issues). The tracker is public, so you can follow progress on your report. It helps to include:

- The address of the page
- What you were trying to do
- What happened instead
- The browser and assistive technology you were using

## Technical details

BMXfeed relies on HTML and CSS only. Conformance was evaluated in Chromium with scripted checks and confirmed by hand in Safari with VoiceOver, Firefox with NVDA, and iOS with VoiceOver.

## About this statement

This statement was prepared on October 10, 2026, by Jason Morris. It’s reviewed after each accessibility audit and whenever a change to templates or styles affects conformance. The format follows the [W3C guidance on accessibility statements](https://www.w3.org/WAI/planning/statements/).
