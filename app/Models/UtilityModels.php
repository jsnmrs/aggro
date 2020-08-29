<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * @file
 * All utility functions.
 */

/**
 * All models for utilities.
 */
class UtilityModels extends Model {

  /**
   * Fetch RSS feed.
   *
   * @param string $feed
   *   RSS feed URL.
   * @param string $spoof
   *   Spoof user agent string (1/0).
   * @param string $cache
   *   Cache duration, in seconds. Default is 30 minutes.
   *
   * @return object
   *   RSS feed data.
   */
  public function fetchFeed($feed, $spoof, $cache = 1800) {
    $userAgent = $_ENV['UA_BMXFEED'];

    if ($spoof == 1) {
      $userAgent = $_ENV['UA_SPOOF'];
    }

    $rss = new \SimplePie();
    $rss->set_cache_location(WRITEPATH . '/cache');
    $rss->set_cache_duration($cache);
    $rss->set_useragent($userAgent);
    $rss->set_item_limit(10);
    $rss->set_timeout(20);
    $rss->set_feed_url($feed);
    $rss->init();

    if ($rss->error()) {
      $errormsg = $feed . " - " . $rss->error();
      log_message('error', $errormsg);
    }

    return $rss;
  }

  /**
   * Get log entries from aggro_log.
   *
   * @return string
   *   Recent log entries.
   */
  public function getLog() {
    $sql = "SELECT * FROM aggro_log ORDER BY log_date DESC LIMIT 250";
    $query = $this->db->query($sql);
    return $query->getResult();
  }

  /**
   * Send message to aggro_log table. Typically non-error messages.
   *
   * @param string $message
   *   Message to insert into aggro_log table.
   *
   * @return bool
   *   Message inserted into aggro_log table.
   */
  public function sendLog($message) {
    $sql = "INSERT INTO aggro_log (log_date, log_message)
            VALUES ('" . date('Y-m-d H:i:s') . "', '" . $message . "')";
    $this->db->query($sql);
    return TRUE;
  }

  /**
   * Update feed data.
   *
   * @param string $slug
   *   Site slug (as ID).
   * @param object $feed
   *   Fetched feed object.
   */
  public function updateFeed($slug, $feed) {
    foreach ($feed->get_items(0, 1) as $item) {
      $lastPost = $item->get_date('Y-m-d H:i:s');
    }

    if (isset($lastPost)) {
      $lastFetch = date('Y-m-d H:i:s');

      $sql = "UPDATE news_feeds SET site_date_last_fetch = '$lastFetch', site_date_last_post = '$lastPost' WHERE site_slug = '$slug'";

      $this->db->query($sql);
    }
  }

}
