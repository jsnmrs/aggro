<?= /**
 * @file
 * Directory RSS feed template.
 */ "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
echo "<channel>\n";
echo "<title>BMXfeed</title>\n";
echo '<link>' . base_url() . "</link>\n";
echo "<description>BMXfeed directory updates</description>\n";
echo '<atom:link href="' . base_url('feed') . "\" rel=\"self\" type=\"application/rss+xml\" />\n";

foreach ($build as $row) {
    echo "<item>\n";
    // Remove control characters and properly encode for XML
    $site_name = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/', ' ', $row->site_name ?? '');
    echo '<title>' . htmlspecialchars($site_name, ENT_XML1 | ENT_QUOTES, 'UTF-8') . " on BMXfeed</title>\n";
    echo '<link>' . base_url('sites/' . $row->site_slug) . "</link>\n";
    echo '<description>';
    echo '<![CDATA[<p>Updated <em>' . htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') . '</em> on BMXfeed.';
    echo ' Check out <a href="' . base_url('sites/' . $row->site_slug) . '">' . base_url('sites/' . $row->site_slug) . '</a> for more info.</p>]]>';
    echo "</description>\n";
    echo '<guid isPermaLink="true">' . base_url('sites/' . $row->site_slug) . "</guid>\n";
    echo '<pubDate>' . date('D, d M Y H:i:s O', strtotime($row->site_date_added ?? '')) . "</pubDate>\n";
    echo "</item>\n";
}
echo "</channel>\n";
echo "</rss>\n";
