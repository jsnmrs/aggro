<?php

/**
 * @file
 * Vimeo helper functions.
 */

if (!function_exists('vimeo_id_from_url')) {

  /**
   * Parse vimeo video id from full URL.
   *
   * @param string $url
   *   Full video URL.
   *
   * @return string
   *   Vimeo id from full URL.
   */
  function vimeo_id_from_url($url) {
    $match = [];
    $pattern = "/vimeo\.com\/([0-9]{1,10})/";
    preg_match($pattern, $url, $match);

    if ($match[1]) {
      return $match[1];
    }

    $pattern = "/player\.vimeo\.com\/video\/([0-9]{1,10})/";
    preg_match($pattern, $url, $match);

    if ($match[1]) {
      return $match[1];
    }

    return FALSE;
  }

}
