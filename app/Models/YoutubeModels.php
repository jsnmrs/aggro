<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All YouTube interactions with aggro_* tables.
 */
class YoutubeModels extends Model {

  /**
   * Parse YouTube feed for videos.
   *
   * @param object $feed
   *   Fetched YouTube feed.
   * @param string $findVideo
   *   Video ID to look for.
   *
   * @return int
   *   Number of videos added.
   */
  public function parseChannel($feed, $findVideo = FALSE) {
    $aggroModel = new AggroModels();
    $utilityModel = new UtilityModels();
    helper('aggro');
    $addCount = 0;
    $now = date('Y-m-d H:i:s');
    $archive = date('Y-m-d H:i:s', strtotime('-31 day', strtotime($now)));

    foreach ($feed->get_items(0, 0) as $item) {
      $video = [];
      $saveVideo = 0;

      // Get video ID.
      $videoId = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'videoId');
      $video['video_id'] = $videoId[0]['data'];

      // If looking for specific video.
      if ($findVideo !== FALSE && $findVideo == $video['video_id']) {
        $saveVideo = 1;
      }

      // If video isn't in DB and not looking for specific.
      if ($findVideo == FALSE && !$aggroModel->checkVideo($video['video_id'])) {
        $saveVideo = 1;
      }

      if ($saveVideo == 1) {
        $video['aggro_date_added'] = $now;
        $video['aggro_date_updated'] = $now;
        $published = $item->get_item_tags('http://www.w3.org/2005/Atom', 'published');
        $video['video_date_uploaded'] = date("Y-m-d H:i:s", strtotime($published[0]['data']));
        $video['flag_bad'] = 0;
        $video['flag_archive'] = 0;
        $video['flag_tweet'] = 1;
        $video['video_type'] = 'youtube';
        if ($video['video_date_uploaded'] <= $archive) {
          $video['flag_archive'] = 1;
          $video['flag_tweet'] = 0;
        }
        $video['video_title'] = $item->get_title();
        $group = $item->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'group');
        $community = $group[0]['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['community'];
        $statistics = $community[0]['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['statistics'];
        $video['video_plays'] = $statistics[0]['attribs']['']['views'];
        $thumbnail = $group[0]['child'][SIMPLEPIE_NAMESPACE_MEDIARSS]['thumbnail'];
        $video['video_thumbnail_url'] = $thumbnail[0]['attribs']['']['url'];
        $channelID = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'channelId');
        $video['video_source_id'] = $channelID[0]['data'];
        $author = $item->get_item_tags('http://www.w3.org/2005/Atom', 'author');
        $authorURL = $author[0]['child']['http://www.w3.org/2005/Atom']['uri'];
        $video['video_source_url'] = $authorURL[0]['data'];
        $authorName = $author[0]['child']['http://www.w3.org/2005/Atom']['name'];
        $video['video_source_username'] = $authorName[0]['data'];

        $oEmbed = "https://www.youtube.com/oembed?format=xml&url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D" . $video['video_id'];
        $result = fetch_url($oEmbed, 'simplexml', 1);
        $video['video_width'] = $result->width;
        $video['video_height'] = $result->height;
        $video['video_aspect_ratio'] = round($result->width / $result->height, 3);
        $video['video_source_user_slug'] = "";
        if (strpos($result->author_url, 'user/') !== FALSE) {
          $video['video_source_user_slug'] = str_replace('https://www.youtube.com/user/', '', $result->author_url);
        }

        // Add $video to DB.
        $sql = "INSERT INTO aggro_videos (video_id, aggro_date_added, aggro_date_updated, video_date_uploaded, flag_archive, flag_bad, video_plays, video_title, video_thumbnail_url, video_width, video_height, video_aspect_ratio, video_source_id, video_source_username, video_source_url, flag_tweet, video_type, video_source_user_slug) VALUES ('" . $video['video_id'] . "', '" . $video['aggro_date_added'] . "', '" . $video['aggro_date_updated'] . "', '" . $video['video_date_uploaded'] . "', " . $video['flag_archive'] . ", 0, " . $video['video_plays'] . ", '" . $video['video_title'] . "', '" . $video['video_thumbnail_url'] . "', " . $video['video_width'] . ", " . $video['video_height'] . ", " . $video['video_aspect_ratio'] . ", '" . $video['video_source_id'] . "', '" . $video['video_source_username'] . "', '" . $video['video_source_url'] . "', " . $video['flag_tweet'] . ", '" . $video['video_type'] . "', '" . $video['video_source_user_slug'] . "')";

        $this->db->query($sql);

        if ($video['flag_archive'] == 0 && fetch_thumbnail($video['video_id'], $video['video_thumbnail_url'])) {
          $message = "Added https://youtube.com/watch?v=" . $video['video_id'] . ", fetched thumbnail.";
        }

        if ($video['flag_archive'] == 1) {
          $message = "Added and archived https://youtube.com/watch?v=" . $video['video_id'] . ".";
        }

        $utilityModel->sendLog($message);

        $addCount++;
      }
    }

    if ($addCount >= 1) {
      $message = "Ran YouTube fetch. Added " . $addCount . " new-to-me videos.";
      $utilityModel->sendLog($message);
    }

    return $addCount;
  }

}
