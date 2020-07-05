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
   *
   * @return string
   *   RSS feed data.
   */
  public function fetchFeed($feed, $spoof) {
    $userAgent = $_ENV['UA_BMXFEED'];

    if ($spoof == 1) {
      $userAgent = $_ENV['UA_SPOOF'];
    }

    $rss = new \SimplePie();
    $rss->set_cache_location(WRITEPATH . '/cache');
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

}
