<?php

/**
 * @file
 * YouTube helper functions.
 */

if (!function_exists('youtube_get_feed')) {

  /**
   * Fetch YouTube channel feed.
   *
   * @param string $feedID
   *   Channel ID, playlist ID, or username.
   *
   * @return object
   *   SimplePie RSS object.
   *
   * @see fetchUrl()
   */
  function youtube_get_feed($feedID) {
    helper('aggro');

    $baseUrl = "https://www.youtube.com/feeds/videos.xml?user=";

    if (substr($feedID, 0, 2) == "UC") {
      $baseUrl = "https://www.youtube.com/feeds/videos.xml?channel_id=";
    }

    if (substr($feedID, 0, 2) == "PL") {
      $baseUrl = "https://www.youtube.com/feeds/videos.xml?playlist_id=";
    }

    $fetch = $baseUrl . $feedID;
    $result = fetch_feed($fetch, 1);

    if ($result !== FALSE && (is_array($result) || is_object($result))) {
      return $result;
    }

    return FALSE;
  }

}

if (!function_exists('youtube_get_meta')) {

  /**
   * Get YouTube video information from YouTube videoID.
   *
   * @param string $videoID
   *   YouTube videoID.
   *
   * @return array
   *   Video metadata.
   *
   * @see fetchUrl()
   */
  function youtube_get_meta($videoID) {
    helper('aggro');
    $video = [];

    $fetch = "https://www.youtube.com/oembed?format=xml&url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D" . $videoID;
    $result = fetch_url($fetch, 'simplexml', 1);

    if ($result !== FALSE && (is_array($result) || is_object($result))) {
      $video['videoID'] = $videoID;
      $video['userUrl'] = $result->author_url;
      $video['width'] = $result->width;
      $video['height'] = $result->height;
      $video['aspectRatio'] = round($result->width / $result->height, 3);
      $video['thumbnail'] = $result->thumbnail_url;
      $video['title'] = $result->title;
      $video['username'] = $result->author_name;

      if (strpos($result->author_url, 'user/') !== FALSE) {
        $video['usernameSlug'] = str_replace('https://www.youtube.com/user/', '', $result->author_url);
      }

      if (strpos($result->author_url, 'channel/') !== FALSE) {
        $video['channelID'] = str_replace('https://www.youtube.com/channel/', '', $result->author_url);
      }

      return $video;
    }

    return FALSE;
  }

}

if (!function_exists('youtube_id_from_url')) {

  /**
   * Parse youtube video id from full URL.
   *
   * @param string $url
   *   Full video URL.
   *
   * @return string
   *   Youtube id from full URL.
   */
  function youtube_id_from_url($url) {
    $match = [];
    $pattern = "/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/";
    preg_match($pattern, $url, $match);

    if ($match[2] && strlen($match[2]) == 11) {
      return $match[2];
    }

    return FALSE;
  }

}
