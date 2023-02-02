<?php

/**
 * @file
 * Vimeo helper functions.
 */

if (!function_exists('vimeo_get_feed')) {

  /**
   * Fetch Vimeo channel feed.
   *
   * @param string $feedID
   *   Channel ID.
   *
   * @return object
   *   JSON object.
   *
   * @see fetchUrl()
   */
  function vimeo_get_feed($feedID) {
    helper('aggro');

    $fetch = "https://vimeo.com/api/v2/" . $feedID . "/videos.json";
    $result = fetch_url($fetch, "json", 0);

    if ($result !== FALSE && (is_array($result) || is_object($result))) {
      return $result;
    }

    return FALSE;
  }

}

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

if (!function_exists('vimeo_parse_meta')) {

  /**
   * Parse vimeo video metadata for DB import.
   *
   * @param object $item
   *   Node from RSS feed.
   *
   * @return array
   *   Video metadata added.
   */
  function vimeo_parse_meta(object $item) {
    $video = [];

    $now = date('Y-m-d H:i:s');
    $archive = date('Y-m-d H:i:s', strtotime('-31 day', strtotime($now)));
    $video['video_id'] = $item->id;
    $video['aggro_date_added'] = $now;
    $video['aggro_date_updated'] = $now;
    $video['video_date_uploaded'] = date("Y-m-d H:i:s", strtotime($item->upload_date));
    $video['flag_bad'] = 0;
    $video['flag_archive'] = 0;
    $video['video_type'] = 'vimeo';
    if ($video['video_date_uploaded'] <= $archive) {
      $video['flag_archive'] = 1;
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

    return $video;
  }

}
