<?php

/**
 * @file
 * Vimeo helper functions.
 */
if (! function_exists('vimeo_get_feed')) {
    /**
     * Fetch Vimeo channel feed.
     *
     * @param string $feedID
     *                       Channel ID.
     *
     * @return false|object
     *                      JSON object, or false on error.
     *
     * @see fetchUrl()
     */
    function vimeo_get_feed($feedID)
    {
        helper('aggro');

        $fetch  = 'https://vimeo.com/api/v2/' . $feedID . '/videos.json';
        $result = fetch_url($fetch, 'json', 0);

        if ($result !== false && (is_array($result) || is_object($result))) {
            return $result;
        }

        // Log error if we got a string response (likely an error message)
        if (is_string($result)) {
            log_message('error', 'Vimeo API returned error: ' . $result);
        }

        return false;
    }
}

if (! function_exists('vimeo_get_plays')) {
    /**
     * Fetch Vimeo video play count.
     *
     * @param string   $videoID
     *                              Vimeo videoID.
     * @param int|null &$httpStatus
     *                              Optional. Populated with the HTTP response code.
     *
     * @param-out int $httpStatus
     *
     * @return false|string|null
     *                           Play count, null when the owner hides stats, or false on error.
     */
    function vimeo_get_plays($videoID, &$httpStatus = null)
    {
        helper('aggro');

        $fetch  = 'https://vimeo.com/api/v2/video/' . $videoID . '.json';
        $result = fetch_url($fetch, 'json', 0, $httpStatus);

        if ($result === false || ! is_array($result) || ! isset($result[0]) || ! is_object($result[0])) {
            return false;
        }

        if (! isset($result[0]->stats_number_of_plays)) {
            return null;
        }

        return (string) $result[0]->stats_number_of_plays;
    }
}

if (! function_exists('vimeo_parse_meta')) {
    /**
     * Parse vimeo video metadata for DB import.
     *
     * @return array
     *               Video metadata added.
     */
    function vimeo_parse_meta(object $item)
    {
        $video = [];

        $now                          = date('Y-m-d H:i:s');
        $archive                      = date('Y-m-d H:i:s', strtotime('-31 day', strtotime($now)));
        $video['video_id']            = $item->id;
        $video['aggro_date_added']    = $now;
        $video['aggro_date_updated']  = $now;
        $video['video_date_uploaded'] = date('Y-m-d H:i:s', strtotime($item->upload_date));
        $video['flag_bad']            = 0;
        $video['flag_short']          = 0;
        $video['flag_archive']        = 0;
        $video['video_type']          = 'vimeo';
        if ($video['video_date_uploaded'] <= $archive) {
            $video['flag_archive'] = 1;
        }
        $video['video_title'] = $item->title;
        $video['video_plays'] = 0;
        if (isset($item->stats_number_of_plays)) {
            $video['video_plays'] = $item->stats_number_of_plays;
        }
        $video['video_thumbnail_url']   = $item->thumbnail_large;
        $video['video_source_id']       = str_replace('https://vimeo.com/', '', $item->user_url);
        $video['video_source_url']      = $item->user_url;
        $video['video_source_username'] = $item->user_name;
        $video['video_width']           = $item->width;
        $video['video_height']          = $item->height;
        $video['video_aspect_ratio']    = ($item->height > 0)
            ? round($item->width / $item->height, 3)
            : 1.778;

        return $video;
    }
}
