<?php

namespace App\Models;

use CodeIgniter\Model;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * All interactions with aggro_* tables.
 */
class AggroModels extends Model {

  /**
   * Archive videos older than 31 days by setting archive flag in video table.
   *
   * Write count of archived videos to log.
   *
   * @return bool
   *   Archive complete.
   *
   * @see sendLog()
   */
  public function archiveVideos() {
    $utilityModel = new UtilityModels();
    $now = date('Y-m-d H:i:s');

    $sql = "SELECT * FROM aggro_videos WHERE video_date_uploaded <= DATE_SUB('" . $now . "',INTERVAL 31 DAY) AND flag_archive=0 AND flag_bad=0";
    $query = $this->db->query($sql);
    $update = count($query->getResultArray());

    if ($update > 0) {
      $sql = "UPDATE aggro_videos SET flag_archive = 1, flag_tweet = 0 WHERE video_date_uploaded <= DATE_SUB('" . $now . "',INTERVAL 31 DAY) AND flag_archive=0 AND flag_bad=0";
      $query = $this->db->query($sql);
    }

    $message = $update . ' videos archived.';
    $utilityModel->sendLog($message);
    return TRUE;
  }

  /**
   * Check if video exists in video table.
   *
   * @param string $videoid
   *   Videoid to check.
   *
   * @return bool
   *   Video exists in video table.
   */
  public function checkVideo($videoid) {
    $utilityModel = new UtilityModels();
    $sql = "SELECT video_id FROM aggro_videos WHERE video_id='" . $videoid . "'";
    $query = $this->db->query($sql);
    $update = count($query->getResultArray());

    if ($update > 0) {
      return TRUE;
    }

    $message = $videoid . ' is new to me.';
    $utilityModel->sendLog($message);
    return FALSE;
  }

  /**
   * Clean thumbnail directory.
   */
  public function cleanThumbs() {
    $utilityModel = new UtilityModels();
    helper('aggro');
    $thumbs = ROOTPATH . "public/thumbs/*.jpg";
    $countBefore = count(glob($thumbs));
    $sql = "SELECT video_id, video_thumbnail_url FROM aggro_videos WHERE flag_archive=0 AND flag_bad=0";
    $query = $this->db->query($sql);
    $countActive = count($query->getResultArray());
    $allThumbs = $query->getResult();
    $source = ROOTPATH . "public/thumbs/";
    $sourceAll = $source . "*.jpg";
    $destination = ROOTPATH . "public/thumbholder/";
    $destinationAll = $destination . "*.jpg";

    if (!file_exists($destination)) {
      mkdir($destination, 0755, TRUE);
    }

    foreach ($allThumbs as $thumb) {
      $current = $thumb->video_id . ".jpg";
      $path = $source . $current;
      if (in_array($current, [".", ".."])) {
        continue;
      }
      if (!file_exists($path)) {
        $message = $thumb->video_id . " missing thumbnail";
        fetch_thumbnail($thumb->video_id, $thumb->video_thumbnail_url);
        $message .= " &mdash; fetched.";
        copy($source . $current, $destination . $current);
        $utilityModel->sendLog($message);
      }

      if (file_exists($path)) {
        copy($source . $current, $destination . $current);
      }
    }

    foreach (glob($sourceAll) as $file) {
      if (is_file($file)) {
        unlink($file);
      }
    }

    foreach (glob($destinationAll) as $file) {
      if (is_file($file)) {
        $destination = str_replace('thumbholder', 'thumbs', $file);
        copy($file, $destination);
      }
    }

    foreach (glob($destinationAll) as $file) {
      if (is_file($file)) {
        unlink($file);
      }
    }

    $countAfter = count(glob($thumbs));

    $message = $countBefore . " thumbs to start, " . $countActive . " active videos, " . $countAfter . " thumbs now.";
    $utilityModel->sendLog($message);

    return TRUE;
  }

  /**
   * Get list of video channels that haven't been updated within timeframe.
   *
   * @param string $stale
   *   Time in minutes to consider a channel stale.
   * @param string $type
   *   Type of channel to grab:
   *   - site.
   *   - youtube.
   *   - vimeo.
   * @param string $limit
   *   Maximum number of channels to grab.
   *
   * @return array
   *   All fields for all video channels matching arguments.
   */
  public function getChannels($stale = 30, $type = "youtube", $limit = 10) {
    $utilityModel = new UtilityModels();
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT * FROM aggro_sources WHERE source_type='" . $type . "' AND source_date_updated <= DATE_SUB('" . $now . "',INTERVAL " . $stale . " MINUTE) ORDER BY source_date_updated ASC LIMIT " . $limit;
    $query = $this->db->query($sql);
    $update = count($query->getResultArray());

    if ($update > 0) {
      $message = 'Looking for ' . $limit . ' ' . $type . ' channels. Found ' . $update . ' stale ' . $type . ' channels. Updating...';
      $utilityModel->sendLog($message);
      return $query->getResult();
    }

    if ($update == 0) {
      $message = 'Looking for ' . $limit . ' ' . $type . ' channels. Found 0 stale ' . $type . ' channels.';
      $utilityModel->sendLog($message);
      return FALSE;
    }
  }

  /**
   * Get single video.
   *
   * @param string $slug
   *   Video id.
   *
   * @return array
   *   Video data from table or FALSE.
   */
  public function getVideo($slug) {
    $slug = esc($slug);
    $sql = "SELECT * FROM aggro_videos WHERE video_id='$slug' LIMIT 1";
    $query = $this->db->query($sql);
    if ($query->getRowArray() == NULL) {
      return FALSE;
    }
    return $query->getRowArray();
  }

  /**
   * Get all videos.
   *
   * @param string $sort
   *   - Recent.
   * @param string $range
   *   - Year.
   *   - Month.
   *   - Week.
   * @param string $perpage
   *   Results per page.
   * @param string $offset
   *   Result starting offset.
   *
   * @return string
   *   Video data from table.
   */
  public function getVideos($sort = 'recent', $range = 'month', $perpage = 10, $offset = 0) {
    $now = date("Y-m-d H:i:s");

    if ($sort == "recent") {
      $sortField = "aggro_date_added";
    }

    if ($range == "year") {
      $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 365 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 SECOND)';
    }

    if ($range == "month") {
      $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 31 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 SECOND)';
    }

    if ($range == "week") {
      $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 7 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 SECOND)';
    }

    $sql = 'SELECT * FROM aggro_videos WHERE flag_bad = 0 AND flag_archive = 0 AND aggro_date_updated <> "0000-00-00 00:00:00"' . $constrict . 'ORDER BY ' . $sortField . ' DESC LIMIT ' . $perpage . ' OFFSET ' . $offset;
    $query = $this->db->query($sql);
    return $query->getResult();
  }

  /**
   * Get all videos total.
   *
   * @return string
   *   Total number of active videos.
   */
  public function getVideosTotal() {
    $sql = "SELECT aggro_id FROM aggro_videos WHERE flag_bad = 0 AND flag_archive = 0 AND aggro_date_updated <> '0000-00-00 00:00:00'";
    $query = $this->db->query($sql);
    return count($query->getResultArray());
  }

  /**
   * Post videos to bmxfeed twitter.
   *
   * Rate-limited.
   *
   * @return bool
   *   Videos tweeted.
   *
   * @see sendLog()
   */
  public function twitterPush() {
    $utilityModel = new UtilityModels();
    $tweetCount = 0;
    $sql = "SELECT * FROM aggro_videos WHERE flag_tweet=1 AND flag_bad = 0 AND flag_archive = 0";
    $query = $this->db->query($sql);
    $update = count($query->getResultArray());

    if ($update > 0) {
      $result = $query->getResult();

      $twitter = new TwitterOAuth($_ENV['CONSUMER_KEY'], $_ENV['CONSUMER_SECRET'], $_ENV['ACCESS_TOKEN'], $_ENV['ACCESS_TOKEN_SECRET']);

      foreach ($result as $row) {
        $cleanTitle = html_entity_decode($row->video_title);
        $tweetText = substr($cleanTitle, 0, 70) . " https://bmxfeed.com/video/" . $row->video_id;
        if ($_ENV['CI_ENVIRONMENT'] == "production") {
          $twitter->post('statuses/update', ['status' => $tweetText]);
        }

        $sql = "UPDATE aggro_videos SET flag_tweet=0 WHERE video_id='" . $row->video_id . "'";
        $this->db->query($sql);
        $tweetCount++;
      }
    }
    if ($tweetCount > 0) {
      $message = $tweetCount . ' tweets.';
      $utilityModel->sendLog($message);
    }

    return TRUE;
  }

  /**
   * Update video source last fetch timestamp.
   *
   * @param string $sourceSlug
   *   Source slug.
   */
  public function updateChannel($sourceSlug) {
    $now = date('Y-m-d H:i:s');
    $sql = "UPDATE aggro_sources
            SET source_date_updated = '" . $now . "'
            WHERE source_slug = '" . $sourceSlug . "'";
    $this->db->query($sql);
  }

}
