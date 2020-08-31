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
   * Convert duration format (PT##M##S) to seconds.
   *
   * @param string $str
   *   Duration string to convert.
   *
   * @return string
   *   Duration converted to seconds.
   */
  public function durationSeconds($str) {
    $seconds = 0;
    $sections = "";
    $result = [];
    preg_match('/^(?:P)([^T]*)(?:T)?(.*)?$/', trim($str), $sections);

    if (!empty($sections[1])) {
      preg_match_all('/(\d+)([YMWD])/', $sections[1], $parts, PREG_SET_ORDER);
      $units = [
        'Y' => 'years',
        'M' => 'months',
        'W' => 'weeks',
        'D' => 'days',
      ];

      foreach ($parts as $part) {
        $result[$units[$part[2]]] = $part[1];
      }
    }

    if (!empty($sections[2])) {
      preg_match_all('/(\d+)([HMS])/', $sections[2], $parts, PREG_SET_ORDER);
      $units = ['H' => 'hours', 'M' => 'minutes', 'S' => 'seconds'];

      foreach ($parts as $part) {
        $result[$units[$part[2]]] = $part[1];
      }
    }

    foreach ($result as $key => $value) {
      switch ($key) {
        case "hours":
          $seconds += $value * 60 * 60;
          break;

        case "minutes":
          $seconds += $value * 60;
          break;

        case "seconds":
          $seconds += $value;
          break;
      }
    }

    return $seconds;
  }

  /**
   * Remove emoji from strings.
   *
   * Lifted from http://stackoverflow.com/a/12824140.
   *
   * @param string $text
   *   String to rinse of emoji.
   *
   * @return string
   *   Clean string, free of emoji.
   */
  public function emojiRemover($text) {
    $cleanText = "";

    // Match Emoticons.
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $cleanText = preg_replace($regexEmoticons, '', $text);

    // Match Miscellaneous Symbols and Pictographs.
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $cleanText = preg_replace($regexSymbols, '', $cleanText);

    // Match Transport And Map Symbols.
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $cleanText = preg_replace($regexTransport, '', $cleanText);

    // Match Miscellaneous Symbols.
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $cleanText = preg_replace($regexMisc, '', $cleanText);

    // Match Dingbats.
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $cleanText = preg_replace($regexDingbats, '', $cleanText);

    // Match Flags.
    $regexDingbats = '/[\x{1F1E6}-\x{1F1FF}]/u';
    $cleanText = preg_replace($regexDingbats, '', $cleanText);

    // Others.
    $regexDingbats = '/[\x{1F910}-\x{1F95E}]/u';
    $cleanText = preg_replace($regexDingbats, '', $cleanText);

    $regexDingbats = '/[\x{1F980}-\x{1F991}]/u';
    $cleanText = preg_replace($regexDingbats, '', $cleanText);

    $regexDingbats = '/[\x{1F9C0}]/u';
    $cleanText = preg_replace($regexDingbats, '', $cleanText);

    $regexDingbats = '/[\x{1F9F9}]/u';
    $cleanText = preg_replace($regexDingbats, '', $cleanText);

    return $cleanText;
  }

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
