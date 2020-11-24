<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All Vimeo interactions with aggro_* tables.
 */
class VimeoModels extends Model {

  /**
   * Parse Vimeo feed for videos.
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

    if ($feed === FALSE) {
      return FALSE;
    }

    if (is_array($feed) || is_object($feed)) {
      foreach ($feed as $item) {
        $video = [];
        $saveVideo = 0;

        // If looking for specific video.
        if ($findVideo !== FALSE && $findVideo == $item->id) {
          $saveVideo = 1;
        }

        // If video isn't in DB and not looking for specific.
        if ($findVideo == FALSE && !$aggroModel->checkVideo($item->id)) {
          $saveVideo = 1;
        }

        if ($saveVideo == 1) {
          $video['video_id'] = $item->id;
          $video['aggro_date_added'] = $now;
          $video['aggro_date_updated'] = $now;
          $video['video_date_uploaded'] = date("Y-m-d H:i:s", strtotime($item->upload_date));
          $video['flag_bad'] = 0;
          $video['flag_archive'] = 0;
          $video['flag_tweet'] = 1;
          $video['video_type'] = 'vimeo';
          if ($video['video_date_uploaded'] <= $archive) {
            $video['flag_archive'] = 1;
            $video['flag_tweet'] = 0;
          }
          $video['video_title'] = htmlentities($item->title, ENT_QUOTES, 'utf-8', FALSE);
          $video['video_plays'] = 0;
          if (isset($item->stats_number_of_plays)) {
            $video['video_plays'] = $item->stats_number_of_plays;
          }
          $video['video_thumbnail_url'] = $item->thumbnail_large;
          $video['video_source_id'] = str_replace('https://vimeo.com/', '', $item->user_url);
          $video['video_source_url'] = $item->user_url;
          $video['video_source_username'] = htmlentities($item->user_name, ENT_QUOTES, 'utf-8', FALSE);
          $video['video_width'] = $item->width;
          $video['video_height'] = $item->height;
          $video['video_aspect_ratio'] = round($item->width / $item->height, 3);
          $video['video_source_user_slug'] = $video['video_source_id'];

          // Add $video to DB.
          $sql = "INSERT INTO aggro_videos (video_id, aggro_date_added, aggro_date_updated, video_date_uploaded, flag_archive, flag_bad, video_plays, video_title, video_thumbnail_url, video_width, video_height, video_aspect_ratio, video_source_id, video_source_username, video_source_url, flag_tweet, video_type, video_source_user_slug) VALUES ('" . $video['video_id'] . "', '" . $video['aggro_date_added'] . "', '" . $video['aggro_date_updated'] . "', '" . $video['video_date_uploaded'] . "', " . $video['flag_archive'] . ", 0, " . $video['video_plays'] . ", '" . $video['video_title'] . "', '" . $video['video_thumbnail_url'] . "', " . $video['video_width'] . ", " . $video['video_height'] . ", " . $video['video_aspect_ratio'] . ", '" . $video['video_source_id'] . "', '" . $video['video_source_username'] . "', '" . $video['video_source_url'] . "', " . $video['flag_tweet'] . ", '" . $video['video_type'] . "', '" . $video['video_source_user_slug'] . "')";

          $this->db->query($sql);

          if ($video['flag_archive'] == 0 && fetch_thumbnail($video['video_id'], $video['video_thumbnail_url'])) {
            $message = "Added https://vimeo.com/" . $video['video_id'] . ", fetched thumbnail.";
          }

          if ($video['flag_archive'] == 1) {
            $message = "Added and archived https://vimeo.com/" . $video['video_id'] . ".";
          }

          $utilityModel->sendLog($message);

          $addCount++;
        }
      }
    }

    if ($addCount >= 1) {
      $message = "Ran Vimeo fetch. Added " . $addCount . " new-to-me videos.";
      $utilityModel->sendLog($message);
    }

    return $addCount;
  }

}
