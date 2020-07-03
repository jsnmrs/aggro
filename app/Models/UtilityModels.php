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
  }

  /**
   * Fetch thumbnail image from video provider, process image, and save locally.
   *
   * @param string $videoid
   *   The videoid.
   * @param string $thumbnail
   *   The remote URL of the video thumbnail.
   *
   * @return bool
   *   Video thumbnail fetched and processed.
   */
  public function fetchThumbnail($videoid, $thumbnail) {
  }

  /**
   * Fetch contents of URL (via CURL). Decode if XML or JSON.
   *
   * @param string $url
   *   URL to be fetched.
   * @param string $format
   *   Format to be returned:
   *   - text: return as text, no decoding.
   *   - simplexml: return as decoded XML.
   *   - json: return as decoded JSON.
   * @param string $spoof
   *   Spoof user agent string (1/0).
   *
   * @return string
   *   Contents of requested url with optional decoding.
   */
  public function fetchUrl($url, $format = "text", $spoof = 0) {
  }

  /**
   * Send message to engine_log table. Typically non-error messages.
   *
   * @param string $message
   *   Message to insert into engine_log table.
   *
   * @return bool
   *   Message inserted into engine_log table.
   */
  public function logger($message) {
  }

}
