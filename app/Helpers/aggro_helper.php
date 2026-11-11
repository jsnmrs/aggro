<?php

use App\Services\ValidationService;
use Config\Services;

/**
 * @file
 * Aggro helper functions.
 */
if (! function_exists('decode_entities')) {
    /**
     * Recursively decode HTML entities until no more decoding is possible.
     *
     * Handles double-encoded and triple-encoded entities common in RSS feeds
     * where content like `&amp;#8211;` needs to become `–`.
     *
     * @param string|null $text Text that may contain encoded HTML entities
     *
     * @return string Text with all HTML entities decoded
     */
    function decode_entities($text)
    {
        if ($text === '' || $text === null) {
            return $text ?? '';
        }

        $previous = '';
        $current  = $text;

        // Keep decoding until no more changes occur
        while ($previous !== $current) {
            $previous = $current;
            $current  = html_entity_decode($current, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $current;
    }
}

if (! function_exists('clean_error_logs')) {
    /**
     * Clean error logs.
     *
     * Logs dated today or later are kept. A log written under a timezone
     * ahead of the current one carries tomorrow's date, and it is still
     * the log in use.
     *
     * @param string|null $directory
     *                               Optional. Log directory with a trailing slash.
     *                               Defaults to the application log directory.
     *
     * @return bool
     *              Logs deleted.
     */
    function clean_error_logs($directory = null)
    {
        $directory ??= WRITEPATH . '/logs/';
        $path  = $directory . '*.log';
        $files = glob($path);
        $today = date('Y-m-d');

        foreach ($files as $file) {
            // CodeIgniter's file handler names log files log-Y-m-d.log
            $current = preg_match('/^log-(\d{4}-\d{2}-\d{2})\.log$/', basename($file), $match) === 1
                && $match[1] >= $today;

            if (is_file($file) && ! $current) {
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
     * @return int
     *             Count of deleted cache files.
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

if (! function_exists('fetch_error_logs')) {
    /**
     * Get error logs.
     *
     * @return list<string>
     *                      Error logs.
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
     * @param string   $feed  RSS feed URL.
     * @param bool|int $spoof Spoof user agent (1 or true = spoof, 0 or false = default).
     * @param int|null $cache Cache duration in seconds. Default is 30 minutes.
     *
     * @return SimplePie\SimplePie RSS feed data.
     */
    function fetch_feed(string $feed, bool|int $spoof, ?int $cache = null)
    {
        $userAgent = env('UA_BMXFEED', 'Aggro/1.0');

        if ((int) $spoof === 1) {
            $userAgent = env('UA_SPOOF', 'Mozilla/5.0 (compatible; Aggro/1.0)');
        }

        $storageConfig = config('Storage');
        $cacheDuration = $cache ?? $storageConfig->defaultCacheDuration;

        $rss = new SimplePie\SimplePie();
        $rss->set_cache_location(rtrim($storageConfig->feedCachePath, '/'));
        $rss->set_cache_duration($cacheDuration);
        $rss->set_useragent($userAgent);
        $rss->set_item_limit($storageConfig->feedItemLimit);
        $rss->set_timeout($storageConfig->feedTimeout);
        $rss->set_feed_url($feed);
        $rss->init();

        if ($rss->error()) {
            $errormsg = $feed . ' - ' . $rss->error();
            log_message('warning', $errormsg);
        }

        return $rss;
    }
}

if (! function_exists('fetch_thumbnail')) {
    /**
     * Fetch thumbnail image from video provider, process image, and save locally.
     *
     * @param string   $videoid
     *                              The videoid.
     * @param string   $thumbnail
     *                              The remote URL of the video thumbnail.
     * @param int|null &$httpStatus
     *                              Optional. Populated with the HTTP status code from the fetch.
     *
     * @param-out int $httpStatus
     *
     * @return bool
     *              Video thumbnail fetched and processed.
     */
    function fetch_thumbnail($videoid, $thumbnail, &$httpStatus = null)
    {
        helper('aggro');
        $storageConfig = config('Storage');
        $path          = $storageConfig->getThumbnailPath($videoid);
        $buffer        = fetch_url($thumbnail, 'text', 0, $httpStatus);

        if (empty($buffer)) {
            return false;
        }

        // The download is kept outside the thumbnail directory until it is
        // processed. A file at the thumbnail path stops the thumbnail from
        // being fetched again, so only a finished thumbnail may land there.
        $download = tempnam(sys_get_temp_dir(), 'thumb');
        if ($download === false) {
            log_message('error', 'Failed to create a temporary file for thumbnail: ' . $path);

            return false;
        }

        try {
            if (! safe_file_write($download, $buffer)) {
                return false;
            }

            Services::image()
                ->withFile($download)
                ->resize($storageConfig->thumbnailWidth, $storageConfig->thumbnailHeight, false, 'width')
                ->convert(IMAGETYPE_WEBP)
                ->save($path, $storageConfig->thumbnailQuality);

            return true;
        } catch (RuntimeException $e) {
            log_message('error', 'Failed to process thumbnail image: ' . $e->getMessage());

            // A failed save can leave partial output behind
            if (is_file($path)) {
                unlink($path);
            }

            return false;
        } finally {
            unlink($download);
        }
    }
}

if (! function_exists('fetch_failure_log_level')) {
    /**
     * Pick the log level for a fetch that failed with an HTTP status.
     *
     * @param int $httpCode
     *                      HTTP status of the failed fetch.
     *
     * @return string
     *                Log level.
     */
    function fetch_failure_log_level($httpCode)
    {
        // A 404 means the content is gone, not an application error,
        // so it stays out of Sentry (which only receives error and above).
        // A 429 is the host being rate limited. Its body is a CAPTCHA page,
        // not the content, so callers must see a failed fetch rather than
        // parse it. It clears on its own, so it also stays at warning.
        $failureLogLevels = [403 => 'error', 404 => 'warning', 429 => 'warning', 500 => 'error'];

        // Most other statuses are transient, such as a 502 or 503 from an
        // overloaded host, so they stay at warning too.
        return $failureLogLevels[$httpCode] ?? 'warning';
    }
}

if (! function_exists('fetch_url')) {
    /**
     * Fetch contents of URL (via CURL). Decode if JSON.
     *
     * @param string   $url
     *                              URL to be fetched.
     * @param string   $format
     *                              Format to be returned:
     *                              - text: return as text, no decoding.
     *                              - json: return as decoded JSON.
     * @param int      $spoof
     *                              Spoof user agent flag (1 = spoof, 0 = default).
     * @param int|null &$httpStatus
     *                              Optional. Populated with the HTTP response code.
     *
     * @param-out int $httpStatus
     *
     * @return array|false|object|string
     *                                   Decoded object/array for json, string for text,
     *                                   or false on error.
     */
    function fetch_url($url, $format = 'text', $spoof = 0, &$httpStatus = null)
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
        curl_setopt($fetch, CURLOPT_TIMEOUT, $storageConfig->urlTimeout);
        curl_setopt($fetch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($fetch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($fetch, CURLOPT_MAXREDIRS, $storageConfig->urlMaxRedirects);
        $response   = curl_exec($fetch);
        $httpCode   = curl_getinfo($fetch, CURLINFO_HTTP_CODE);
        $httpStatus = $httpCode;
        $errorInfo  = curl_error($fetch);
        curl_close($fetch);

        // The body of a 4xx or 5xx response is an error page, not the
        // content, so callers must see a failed fetch rather than parse it.
        if ($httpCode >= 400) {
            $message = $url . ' returned ' . $httpCode . '.';
            log_message(fetch_failure_log_level($httpCode), $message);

            return false;
        }

        // Transport failures (DNS, connect, or transfer timeouts) carry no
        // HTTP status and are usually transient. Callers count repeated
        // failures and retire persistent offenders at error level, so the
        // per-attempt message stays at warning to keep Sentry quiet.
        if (empty($response)) {
            $message = $url . ' returned no data. Error: ' . $errorInfo;
            log_message('warning', $message);

            return false;
        }

        if ($format === 'json') {
            $response = json_decode($response);
        }

        return $response;
    }
}

if (! function_exists('fetch_url_status')) {
    /**
     * Fetch only the HTTP status of a URL, without following redirects.
     *
     * Some endpoints answer a question with the status line alone, such
     * as a redirect that says where a resource really lives. The body is
     * discarded and never inspected, so a page that is itself a bot wall
     * or CAPTCHA cannot be mistaken for content. Every status is a valid
     * answer for a probe, so nothing is logged here; the caller decides
     * what counts as inconclusive.
     *
     * @param string      $url
     *                                  URL to request.
     * @param int         $spoof
     *                                  Use spoofed user agent.
     * @param string|null &$redirectUrl
     *                                  Optional. Populated with the Location target
     *                                  when the response is a redirect, otherwise ''.
     *
     * @param-out string $redirectUrl
     *
     * @return int
     *             HTTP status code, or 0 when the request never completed.
     */
    function fetch_url_status($url, $spoof = 0, &$redirectUrl = null): int
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
        curl_setopt($fetch, CURLOPT_TIMEOUT, $storageConfig->urlTimeout);
        curl_setopt($fetch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($fetch, CURLOPT_FOLLOWLOCATION, false);
        curl_exec($fetch);
        $httpCode    = (int) curl_getinfo($fetch, CURLINFO_HTTP_CODE);
        $redirectUrl = (string) curl_getinfo($fetch, CURLINFO_REDIRECT_URL);
        curl_close($fetch);

        return $httpCode;
    }
}

if (! function_exists('gate_check')) {
    /**
     * Check request context for pass through.
     *
     * @return bool
     *              CLI, development, or valid gate parameter.
     */
    function gate_check()
    {
        // Allow CLI and development environments
        if (is_cli() || env('CI_ENVIRONMENT', 'production') === 'development') {
            return true;
        }

        // Check for valid gate parameter
        $validationService = new ValidationService();

        $request = Services::request();
        $gate    = $request->getGet('g');

        if (! $validationService->validateGateKey($gate)) {
            return false;
        }

        // Compare with environment variable using timing-safe comparison
        $envGate = getenv('GATE');

        if ($envGate === false || $envGate === '') {
            log_message('warning', 'GATE environment variable is not set');

            return false;
        }

        return hash_equals($envGate, $gate);
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
