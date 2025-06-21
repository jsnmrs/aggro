<?= /**
 * @file
 * OPML feed template.
 */ "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<opml version=\"1.0\">\n";
echo "<head>\n";
echo "<title>BMXfeed OPML</title>\n";
echo "</head>\n";
echo "<body>\n";
echo "<outline text=\"BMXfeed\">\n";

foreach ($build as $row) {
    // Remove control characters from all fields
    $site_name = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/', ' ', $row->site_name ?? '');
    $site_feed = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/', ' ', $row->site_feed ?? '');
    $site_url  = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/', ' ', $row->site_url ?? '');

    echo '<outline title="' . htmlspecialchars($site_name, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '" text="' . htmlspecialchars($site_name, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '" type="rss" xmlUrl="' . htmlspecialchars($site_feed, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '" htmlUrl="' . htmlspecialchars($site_url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "\" />\n";
}
echo "</outline>\n";
echo "</body>\n";
echo '</opml>';
