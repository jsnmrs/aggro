<?php

/**
 * @file
 * OPML feed template.
 */

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<opml version=\"1.0\">\n";
echo "<head>\n";
echo "<title>BMXfeed OPML</title>\n";
echo "</head>\n";
echo "<body>\n";
echo "<outline text=\"BMXfeed\">\n";
foreach ($build as $row) {
  echo "<outline title=\"" . htmlspecialchars($row->site_name) . "\" text=\"" . htmlspecialchars($row->site_name) . "\" type=\"rss\" xmlUrl=\"" . htmlspecialchars($row->site_feed) . "\" htmlUrl=\"" . htmlspecialchars($row->site_url) . "\" />\n";
}
echo "</outline>\n";
echo "</body>\n";
echo "</opml>";
