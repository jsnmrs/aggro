<?php

/**
 * @file
 * Video RSS feed template.
 */

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">\n";
echo "<channel>\n";
echo "<title>bmxfeed video</title>\n";
echo "<link>https://bmxfeed.com/video/</link>\n";
echo "<description>Recently spotted videos on bmxfeed.com</description>\n";
foreach ($build as $row) {
  echo "<item>\n";
  $videoTitle = htmlspecialchars($row->video_title, ENT_QUOTES | ENT_IGNORE, "UTF-8");
  $videoTitle = str_replace(",", "", $videoTitle);

  echo "<title>BMXFEED: " . $videoTitle . "</title>\n";
  echo "<link>https://bmxfeed.com/video/" . $row->video_id . "/</link>\n";
  echo "<description>Uploaded by " . htmlspecialchars($row->video_source_username, ENT_QUOTES | ENT_IGNORE, "UTF-8") . " on " . date("F j g:ia", strtotime($row->video_date_uploaded)) . ".</description>\n";
  echo "<content:encoded>\n";
  echo "<![CDATA[";
  echo "<p>Spotted <a href=\"https://bmxfeed.com/video/" . $row->video_id . "/\">" . $row->video_title . "</a>. Uploaded by <a href=\"" . $row->video_source_url . "\">" . htmlspecialchars($row->video_source_username, ENT_QUOTES | ENT_IGNORE, "UTF-8") . "</a> on " . date("F j g:ia", strtotime($row->video_date_uploaded)) . ".</p>\n";
  if ($row->video_type == "vimeo") {
    echo "<p><iframe src=\"https://player.vimeo.com/video/" . $row->video_id . "?dnt=true&amp;portrait=0&amp;byline=0&amp;title=0&amp;autoplay=0&amp;color=ffffff\" width=\"" . $row->video_width . "\" height=\"" . $row->video_height . "\" frameborder=\"0\" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></p>";
  }
  if ($row->video_type == "youtube") {
    echo "<p><iframe width=\"" . $row->video_width . "\" height=\"" . $row->video_height . "\" src=\"https://www.youtube-nocookie.com/embed/" . $row->video_id . "?rel=0&amp;showinfo=0\" frameborder=\"0\" allowfullscreen></iframe></p>";
  }
  echo "]]>";
  echo "</content:encoded>\n";
  echo "<guid isPermaLink=\"true\">https://bmxfeed.com/video/" . $row->video_id . "</guid>\n";
  echo "<pubDate>" . date("D, d M Y H:i:s O", strtotime($row->aggro_date_added)) . "</pubDate>\n";
  echo "</item>\n";
}
echo "</channel>\n";
echo "</rss>\n";
