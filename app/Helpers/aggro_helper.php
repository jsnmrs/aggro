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
        // Currently disabled - emoji cleaning functionality not needed
        // If emoji cleaning is required in the future, implement using a modern emoji library
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
        $storageConfig = config('Storage');
        $path          = $storageConfig->getThumbnailPath($videoid);

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
                $fileSize = filesize($file);
                if ($fileSize === false) {
                    log_message('error', 'Failed to get file size for: ' . $file);

                    continue;
                }

                $myfile = fopen($file, 'rb');
                if ($myfile === false) {
                    log_message('error', 'Failed to open log file for reading: ' . $file);

                    continue;
                }

                $content = fread($myfile, $fileSize);
                if ($content === false) {
                    log_message('error', 'Failed to read log file: ' . $file);
                    fclose($myfile);

                    continue;
                }

                if (fclose($myfile) === false) {
                    log_message('error', 'Failed to close log file: ' . $file);
                }

                $results[] = $content;
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
    function fetch_feed($feed, $spoof, $cache = null)
    {
        $userAgent = env('UA_BMXFEED', 'Aggro/1.0');

        if ($spoof === 1) {
            $userAgent = env('UA_SPOOF', 'Mozilla/5.0 (compatible; Aggro/1.0)');
        }

        $storageConfig = config('Storage');
        $cacheDuration = $cache ?? $storageConfig->defaultCacheDuration;

        $rss = new SimplePie\SimplePie();
        $rss->set_cache_location(WRITEPATH . '/cache');
        $rss->set_cache_duration($cacheDuration);
        $rss->set_useragent($userAgent);
        $rss->set_item_limit($storageConfig->feedItemLimit);
        $rss->set_timeout($storageConfig->feedTimeout);
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
        $storageConfig = config('Storage');
        $path          = $storageConfig->getThumbnailPath($videoid);
        $buffer        = fetch_url($thumbnail);

        if (! empty($buffer)) {
            $file = fopen($path, 'wb');
            if ($file === false) {
                log_message('error', 'Failed to open thumbnail file for writing: ' . $path);

                return false;
            }

            $bytesWritten = fwrite($file, $buffer, strlen($buffer));
            if ($bytesWritten === false || $bytesWritten !== strlen($buffer)) {
                log_message('error', 'Failed to write complete thumbnail data to file: ' . $path);
                fclose($file);

                return false;
            }

            if (fclose($file) === false) {
                log_message('error', 'Failed to close thumbnail file: ' . $path);

                return false;
            }

            try {
                Services::image()
                    ->withFile($path)
                    ->resize($storageConfig->thumbnailWidth, $storageConfig->thumbnailHeight, false, 'width')
                    ->convert(IMAGETYPE_WEBP)
                    ->save($path, $storageConfig->thumbnailQuality);

                return true;
            } catch (RuntimeException $e) {
                log_message('error', 'Failed to process thumbnail image: ' . $e->getMessage());

                return false;
            }
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
        $storageConfig = config('Storage');
        $agent         = env('UA_BMXFEED', 'Aggro/1.0');
        if ($spoof === 1) {
            $agent = env('UA_SPOOF', 'Mozilla/5.0 (compatible; Aggro/1.0)');
        }
        $fetch = curl_init();
        curl_setopt($fetch, CURLOPT_URL, $url);
        curl_setopt($fetch, CURLOPT_USERAGENT, $agent);
        curl_setopt($fetch, CURLOPT_CONNECTTIMEOUT, $storageConfig->urlConnectTimeout);
        curl_setopt($fetch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($fetch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($fetch, CURLOPT_MAXREDIRS, $storageConfig->urlMaxRedirects);
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
        return (bool) (is_cli() || env('CI_ENVIRONMENT', 'production') === 'development');
    }
}

if (! function_exists('safe_file_write')) {
    /**
     * Safely write data to a file with error handling.
     *
     * @param string $path The file path to write to
     * @param string $data The data to write
     * @param string $mode The file open mode (default: 'wb')
     *
     * @return bool True on success, false on failure
     */
    function safe_file_write($path, $data, $mode = 'wb')
    {
        $file = fopen($path, $mode);
        if ($file === false) {
            log_message('error', 'Failed to open file for writing: ' . $path);

            return false;
        }

        $dataLength   = strlen($data);
        $bytesWritten = fwrite($file, $data, $dataLength);

        if ($bytesWritten === false || $bytesWritten !== $dataLength) {
            log_message('error', 'Failed to write complete data to file: ' . $path . ' (wrote ' . $bytesWritten . ' of ' . $dataLength . ' bytes)');
            fclose($file);

            return false;
        }

        if (fclose($file) === false) {
            log_message('error', 'Failed to close file after writing: ' . $path);

            return false;
        }

        return true;
    }
}

if (! function_exists('safe_file_read')) {
    /**
     * Safely read data from a file with error handling.
     *
     * @param string $path The file path to read from
     * @param string $mode The file open mode (default: 'rb')
     *
     * @return false|string File contents on success, false on failure
     */
    function safe_file_read($path, $mode = 'rb')
    {
        if (! is_file($path)) {
            log_message('error', 'File does not exist: ' . $path);

            return false;
        }

        $fileSize = filesize($path);
        if ($fileSize === false) {
            log_message('error', 'Failed to get file size: ' . $path);

            return false;
        }

        // Handle empty files
        if ($fileSize === 0) {
            return '';
        }

        $file = fopen($path, $mode);
        if ($file === false) {
            log_message('error', 'Failed to open file for reading: ' . $path);

            return false;
        }

        $content = fread($file, $fileSize);
        if ($content === false) {
            log_message('error', 'Failed to read file: ' . $path);
            fclose($file);

            return false;
        }

        if (fclose($file) === false) {
            log_message('error', 'Failed to close file after reading: ' . $path);
        }

        return $content;
    }
}
