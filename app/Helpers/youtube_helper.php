<?php

/**
 * @file
 * YouTube helper functions.
 */
if (! function_exists('youtube_get_duration')) {
    /**
     * Fetch YouTube video duration.
     *
     * @param string $videoID
     *                        YouTube videoID.
     *
     * @return string
     *                Video duration.
     */
    function youtube_get_duration($videoID)
    {
        helper('aggro');

        $videoPage  = 'https://www.youtube.com/watch?v=' . $videoID;
        $resultPage = fetch_url($videoPage, 'text', 0);

        if ($resultPage !== false && is_string($resultPage)) {
            if (preg_match('/"lengthSeconds":"(\d+)"/', $resultPage, $matches)) {
                if ($matches[1] > 0) {
                    return $matches[1];
                }
            }
        }

        return false;
    }
}

if (! function_exists('youtube_get_feed')) {
    /**
     * Fetch YouTube channel feed.
     *
     * @param string $feedID
     *                       Channel ID, playlist ID, or username.
     *
     * @return object
     *                SimplePie RSS object.
     *
     * @see fetchUrl()
     */
    function youtube_get_feed($feedID)
    {
        helper('aggro');

        $baseUrl = 'https://www.youtube.com/feeds/videos.xml?user=';

        if (substr($feedID, 0, 2) === 'UC') {
            $baseUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id=';
        }

        if (substr($feedID, 0, 2) === 'PL') {
            $baseUrl = 'https://www.youtube.com/feeds/videos.xml?playlist_id=';
        }

        $fetch  = $baseUrl . $feedID;
        $result = fetch_feed($fetch, 1);

        if ($result !== false && (is_array($result) || is_object($result))) {
            return $result;
        }

        return false;
    }
}

if (! function_exists('youtube_get_video_source')) {
    /**
     * Get YouTube sourceID from YouTube videoID.
     *
     * @param string $videoID
     *                        YouTube videoID.
     *
     * @return string
     *                Video sourceID.
     *
     * @see fetchUrl()
     */
    function youtube_get_video_source($videoID)
    {
        helper('aggro');
        $canonicalRegex = '/<link rel="canonical" href="https:\\/\\/www.youtube.com\\/channel\\/(.*?)">/';

        $fetch  = 'https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D' . $videoID;
        $result = fetch_url($fetch, 'json', 0);

        if ($result === false || ! (is_array($result) || is_object($result))) {
            return false;
        }

        $channelURL = (string) ($result->author_url);

        if (substr(str_replace('https://www.youtube.com/channel/', '', $channelURL), 0, 2) === 'UC') {
            return str_replace('https://www.youtube.com/channel/', '', $channelURL);
        }

        $channelResult = fetch_url($channelURL, 'text', 1);
        preg_match($canonicalRegex, $channelResult, $matches);

        if ($matches[1]) {
            return $matches[1];
        }

        return false;
    }
}

if (! function_exists('youtube_id_from_url')) {
    /**
     * Parse youtube video id from full URL.
     *
     * @param string $url
     *                    Full video URL.
     *
     * @return string
     *                Youtube id from full URL.
     */
    function youtube_id_from_url($url)
    {
        $match   = [];
        $pattern = '/^.*(youtu.be\\/|v\\/|u\\/\\w\\/|embed\\/|watch\\?v=|\\&v=)([^#\\&\\?]*).*/';
        preg_match($pattern, $url, $match);

        if (isset($match[2]) && strlen($match[2]) === 11) {
            // Add validation to ensure clean ID
            $id = trim($match[2]);
            if (preg_match('/^[\w-]{11}$/', $id)) {
                return $id;
            }
        }

        return false;
    }
}

if (! function_exists('youtube_parse_meta')) {
    /**
     * Parse youtube video metadata for DB import.
     *
     * @return array
     *               Video metadata added.
     */
    function youtube_parse_meta(object $item)
    {
        helper('aggro');
        $video = [];

        $now                          = date('Y-m-d H:i:s');
        $archive                      = date('Y-m-d H:i:s', strtotime('-31 day', strtotime($now)));
        $currentVideo                 = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'videoId');
        $video['video_id']            = $currentVideo[0]['data'];
        $video['aggro_date_added']    = $now;
        $video['aggro_date_updated']  = $now;
        $published                    = $item->get_item_tags('http://www.w3.org/2005/Atom', 'published');
        $video['video_date_uploaded'] = date('Y-m-d H:i:s', strtotime($published[0]['data']));
        $video['flag_bad']            = 0;
        $video['flag_archive']        = 0;
        $video['video_type']          = 'youtube';
        if ($video['video_date_uploaded'] <= $archive) {
            $video['flag_archive'] = 1;
        }
        $video['video_title']           = $item->get_title();
        $group                          = $item->get_item_tags(SimplePie\SimplePie::NAMESPACE_MEDIARSS, 'group');
        $community                      = $group[0]['child'][SimplePie\SimplePie::NAMESPACE_MEDIARSS]['community'];
        $statistics                     = $community[0]['child'][SimplePie\SimplePie::NAMESPACE_MEDIARSS]['statistics'];
        $video['video_plays']           = $statistics[0]['attribs']['']['views'];
        $thumbnail                      = $group[0]['child'][SimplePie\SimplePie::NAMESPACE_MEDIARSS]['thumbnail'];
        $video['video_thumbnail_url']   = $thumbnail[0]['attribs']['']['url'];
        $channelID                      = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'channelId');
        $video['video_source_id']       = $channelID[0]['data'];
        $author                         = $item->get_item_tags('http://www.w3.org/2005/Atom', 'author');
        $authorURL                      = $author[0]['child']['http://www.w3.org/2005/Atom']['uri'];
        $video['video_source_url']      = $authorURL[0]['data'];
        $authorName                     = $author[0]['child']['http://www.w3.org/2005/Atom']['name'];
        $video['video_source_username'] = $authorName[0]['data'];

        $oEmbed                = 'https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D' . $video['video_id'];
        $result                = fetch_url($oEmbed, 'json', 0);
        $video['video_width']  = 800;
        $video['video_height'] = 450;
        if ($result !== false && (is_array($result) || is_object($result))) {
            $video['video_width']  = $result->width;
            $video['video_height'] = $result->height;
        }
        $video['video_aspect_ratio'] = round($video['video_width'] / $video['video_height'], 3);

        $video['video_duration'] = youtube_get_duration($video['video_id']);

        if (! $video['video_duration']) {
            $video['video_duration'] = 0;
        }

        return $video;
    }
}
