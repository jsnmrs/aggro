<?php

use Config\Services;

/**
 * @file
 * Aggro helper functions.
 */
if (! function_exists('clean_emoji')) {
    /**
     * Remove emoji from strings.
     *
     * Lifted from http://stackoverflow.com/a/12824140.
     *
     * @param string $text
     *                     String to rinse of emoji.
     *
     * @return string
     *                Clean string, free of emoji.
     */
    function clean_emoji($text)
    {
        // $cleanText = '';

        // // Match Emoticons.
        // $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        // $cleanText      = preg_replace($regexEmoticons, '', $text);

        // // Match Miscellaneous Symbols and Pictographs.
        // $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        // $cleanText    = preg_replace($regexSymbols, '', $cleanText);

        // // Match Transport And Map Symbols.
        // $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        // $cleanText      = preg_replace($regexTransport, '', $cleanText);

        // // Match Miscellaneous Symbols.
        // $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        // $cleanText = preg_replace($regexMisc, '', $cleanText);

        // // Match Dingbats.
        // $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        // $cleanText     = preg_replace($regexDingbats, '', $cleanText);

        // // Match Flags.
        // $regexDingbats = '/[\x{1F1E6}-\x{1F1FF}]/u';
        // $cleanText     = preg_replace($regexDingbats, '', $cleanText);

        // // Others.
        // $regexDingbats = '/[\x{1F910}-\x{1F95E}]/u';
        // $cleanText     = preg_replace($regexDingbats, '', $cleanText);

        // $regexDingbats = '/[\x{1F980}-\x{1F991}]/u';
        // $cleanText     = preg_replace($regexDingbats, '', $cleanText);

        // $regexDingbats = '/[\x{1F9C0}]/u';
        // $cleanText     = preg_replace($regexDingbats, '', $cleanText);

        // $regexDingbats = '/[\x{1F9F9}]/u';

        // return preg_replace($regexDingbats, '', $cleanText);
        return $text;
    }
}

if (! function_exists('clean_error_log')) {
    /**
     * Clean error logs.
     *
     * @return bool
     *              Logs deleted.
     */
    function clean_error_logs()
    {
        $path     = WRITEPATH . '/logs/*.log';
        $files    = glob($path);
        $todaylog = WRITEPATH . '/logs/' . date('Y-m-d') . '.log';

        foreach ($files as $file) {
            if (is_file($file) && ($file !== $todaylog)) {
                unlink($file);
            }
        }

        return true;
    }
}

if (! function_exists('clean_feed_cache')) {
    /**
     * Delete feed cache.
     *
     * @return string
     *                Count of deleted cache files.
     */
    function clean_feed_cache()
    {
        $counter = 0;
        $path    = WRITEPATH . '/cache/*.spc';
        $files   = glob($path);

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $counter++;
            }
        }

        return $counter;
    }
}

if (! function_exists('clean_thumbnail')) {
    /**
     * Delete thumbnail.
     *
     * @param string $videoid
     *                        Video id.
     *
     * @return bool
     *              Thumbnail deleted.
     */
    function clean_thumbnail($videoid)
    {
        $path = ROOTPATH . 'public/thumbs/' . $videoid . 'webp';
        if (file_exists($path)) {
            unlink($path);
        }

        return true;
    }
}

if (! function_exists('fetch_error_logs')) {
    /**
     * Get error logs.
     *
     * @return string
     *                Error logs.
     */
    function fetch_error_logs()
    {
        $results = [];
        $path    = WRITEPATH . '/logs/*.log';
        $files   = glob($path);

        foreach ($files as $file) {
            if (is_file($file)) {
                $myfile    = fopen($file, 'rb');
                $results[] = fread($myfile, filesize($file));
                fclose($myfile);
            }
        }

        return $results;
    }
}

if (! function_exists('fetch_feed')) {
    /**
     * Fetch RSS feed.
     *
     * @param string $feed
     *                      RSS feed URL.
     * @param string $spoof
     *                      Spoof user agent string (1/0).
     * @param string $cache
     *                      Cache duration, in seconds. Default is 30 minutes.
     *
     * @return object
     *                RSS feed data.
     */
    function fetch_feed($feed, $spoof, $cache = 1800)
    {
        $userAgent = $_ENV['UA_BMXFEED'];

        if ($spoof === 1) {
            $userAgent = $_ENV['UA_SPOOF'];
        }

        $rss = new SimplePie\SimplePie();
        $rss->set_cache_location(WRITEPATH . '/cache');
        $rss->set_cache_duration($cache);
        $rss->set_useragent($userAgent);
        $rss->set_item_limit(10);
        $rss->set_timeout(20);
        $rss->set_feed_url($feed);
        $rss->init();

        if ($rss->error()) {
            $errormsg = $feed . ' - ' . $rss->error();
            log_message('error', $errormsg);
        }

        return $rss;
    }
}

if (! function_exists('fetch_thumbnail')) {
    /**
     * Fetch thumbnail image from video provider, process image, and save locally.
     *
     * @param string $videoid
     *                          The videoid.
     * @param string $thumbnail
     *                          The remote URL of the video thumbnail.
     *
     * @return bool
     *              Video thumbnail fetched and processed.
     */
    function fetch_thumbnail($videoid, $thumbnail)
    {
        helper('aggro');
        $path   = ROOTPATH . 'public/thumbs/' . $videoid . '.webp';
        $buffer = fetch_url($thumbnail);

        if (! empty($buffer)) {
            $file = fopen($path, 'wb');
            fwrite($file, $buffer, strlen($buffer));
            fclose($file);

            Services::image()
                ->withFile($path)
                ->resize(600, 338, false, 'width')
                ->convert(IMAGETYPE_WEBP)
                ->save($path, 40);

            return true;
        }

        return false;
    }
}

if (! function_exists('fetch_url')) {
    /**
     * Fetch contents of URL (via CURL). Decode if XML or JSON.
     *
     * @param string $url
     *                       URL to be fetched.
     * @param string $format
     *                       Format to be returned:
     *                       - text: return as text, no decoding.
     *                       - simplexml: return as decoded XML.
     *                       - json: return as decoded JSON.
     * @param string $spoof
     *                       Spoof user agent string (1/0).
     *
     * @return string
     *                Contents of requested url with optional decoding.
     */
    function fetch_url($url, $format = 'text', $spoof = 0)
    {
        $agent = $_ENV['UA_BMXFEED'];
        if ($spoof === 1) {
            $agent = $_ENV['UA_SPOOF'];
        }
        $fetch = curl_init();
        curl_setopt($fetch, CURLOPT_URL, $url);
        curl_setopt($fetch, CURLOPT_USERAGENT, $agent);
        curl_setopt($fetch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($fetch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($fetch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($fetch, CURLOPT_MAXREDIRS, 4);
        $response  = curl_exec($fetch);
        $httpCode  = curl_getinfo($fetch, CURLINFO_HTTP_CODE);
        $errorInfo = curl_error($fetch);
        curl_close($fetch);

        if ($httpCode === 403 || $httpCode === 404 || $httpCode === 500) {
            $message = $url . ' returned ' . $httpCode . '.';
            log_message('error', $message);

            return false;
        }

        if (empty($response)) {
            $message = $url . ' returned no data. Error: ' . $errorInfo;
            log_message('error', $message);

            return false;
        }

        if ($format === 'simplexml') {
            libxml_use_internal_errors(true);
            $response = simplexml_load_string($response);
            $errors   = libxml_get_errors();

            if (! empty($errors)) {
                $message = $url . ' is throwing XML errors.';
                log_message('error', $message);

                return false;
            }
        }

        if ($format === 'json') {
            $response = json_decode($response);
        }

        return $response;
    }
}

if (! function_exists('gate_check')) {
    /**
     * Check request context for pass through.
     *
     * @return bool
     *              CLI or development.
     */
    function gate_check()
    {
        return (bool) (is_cli() || $_ENV['CI_ENVIRONMENT'] === 'development');
    }
}
