<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestLogger;
use SimplePie\SimplePie;
use Tests\Support\LocalHttpServerTrait;
use TypeError;

/**
 * @internal
 */
final class AggroHelperTest extends CIUnitTestCase
{
    use LocalHttpServerTrait;

    protected function setUp(): void
    {
        parent::setUp();
        helper('aggro');
    }

    public function testCleanErrorLogsMethodExists(): void
    {
        $this->assertTrue(function_exists('clean_error_logs'));
    }

    public function testCleanErrorLogsReturnsBoolean(): void
    {
        $result = clean_error_logs();
        $this->assertIsBool($result);
    }

    public function testCleanErrorLogsKeepsLogsDatedTodayOrLaterAndRemovesOlderLogs(): void
    {
        $directory = sys_get_temp_dir() . '/aggro-logs-' . uniqid() . '/';
        mkdir($directory);

        // CodeIgniter's file handler names log files log-Y-m-d.log
        $today = $directory . 'log-' . date('Y-m-d') . '.log';
        // A log written under a timezone ahead of this one carries tomorrow's date
        $tomorrow = $directory . 'log-' . date('Y-m-d', strtotime('+1 day')) . '.log';
        $older    = $directory . 'log-2000-01-01.log';
        $undated  = $directory . 'undated.log';
        $other    = $directory . 'index.html';

        file_put_contents($today, 'today');
        file_put_contents($tomorrow, 'tomorrow');
        file_put_contents($older, 'older');
        file_put_contents($undated, 'undated');
        file_put_contents($other, 'not a log');

        try {
            $this->assertTrue(clean_error_logs($directory));

            $this->assertFileExists($today);
            $this->assertFileExists($tomorrow);
            $this->assertFileDoesNotExist($older);
            $this->assertFileDoesNotExist($undated);
            $this->assertFileExists($other);
        } finally {
            array_map('unlink', glob($directory . '*'));
            rmdir($directory);
        }
    }

    public function testCleanFeedCacheMethodExists(): void
    {
        $this->assertTrue(function_exists('clean_feed_cache'));
    }

    public function testCleanFeedCacheReturnsInteger(): void
    {
        $result = clean_feed_cache();
        $this->assertIsInt($result);
    }

    public function testFetchErrorLogsMethodExists(): void
    {
        $this->assertTrue(function_exists('fetch_error_logs'));
    }

    public function testFetchErrorLogsReturnsArray(): void
    {
        $result = fetch_error_logs();
        $this->assertIsArray($result);
    }

    public function testFetchFeedMethodExists(): void
    {
        $this->assertTrue(function_exists('fetch_feed'));
    }

    public function testFetchFeedWithValidParameters(): void
    {
        // The fetch is blocked, so the feed comes back carrying an error
        $result = fetch_feed('invalid-url', 0);
        $this->assertInstanceOf(SimplePie::class, $result);
        $this->assertNotNull($result->error());
    }

    public function testFetchThumbnailMethodExists(): void
    {
        $this->assertTrue(function_exists('fetch_thumbnail'));
    }

    public function testFetchThumbnailWithValidParameters(): void
    {
        // The fetch is blocked, so there is no image to save
        $result = fetch_thumbnail('test123', 'https://example.com/thumb.jpg');
        $this->assertFalse($result);
    }

    public function testFetchUrlMethodExists(): void
    {
        $this->assertTrue(function_exists('fetch_url'));
    }

    public function testFetchUrlWithInvalidUrl(): void
    {
        $result = fetch_url('invalid-url');
        $this->assertFalse($result);
    }

    public function testFetchUrlLogsTransportFailureAtWarning(): void
    {
        $result = fetch_url('invalid-url', 'text', 0, $httpStatus);

        // Transport failures never reach Sentry; callers retire repeat offenders
        $this->assertFalse($result);
        $this->assertSame(0, $httpStatus);
        $this->assertLogContains('warning', 'invalid-url returned no data.');
        $this->assertFalse(TestLogger::didLog('error', 'invalid-url returned no data.', false));
    }

    public function testFetchUrlWithTextFormat(): void
    {
        $result = fetch_url('invalid-url', 'text');
        $this->assertFalse($result);
    }

    public function testFetchUrlWithJsonFormat(): void
    {
        $result = fetch_url('invalid-url', 'json');
        $this->assertFalse($result);
    }

    public function testGateCheckMethodExists(): void
    {
        $this->assertTrue(function_exists('gate_check'));
    }

    public function testGateCheckReturnsBoolean(): void
    {
        $result = gate_check();
        $this->assertIsBool($result);
    }

    public function testGateCheckWithoutGateParameter(): void
    {
        unset($_GET['g']);
        $result = gate_check();
        // gate_check behavior may vary based on environment
        $this->assertIsBool($result);
    }

    public function testGateCheckWithValidGateParameter(): void
    {
        $_GET['g'] = 'test-gate-value';
        $result    = gate_check();
        // Result depends on environment configuration
        $this->assertIsBool($result);
    }

    public function testGateCheckReturnsEarlyInCli(): void
    {
        // gate_check returns true early in CLI mode, so we verify that behavior
        // The GATE env var check only runs in non-CLI, non-development environments
        $this->assertTrue(is_cli(), 'Test environment should be CLI');
        $result = gate_check();
        $this->assertTrue($result, 'gate_check should return true in CLI mode');
    }

    /**
     * Test that hash_equals would throw TypeError with boolean false.
     *
     * This tests the core issue (#750): when GATE env var is not set,
     * getenv() returns false (boolean), and hash_equals() requires strings.
     * The fix should check for false/empty before calling hash_equals.
     */
    public function testHashEqualsWithBooleanFalseThrowsTypeError(): void
    {
        // Demonstrate the bug: hash_equals throws TypeError when first arg is false
        $this->expectException(TypeError::class);
        hash_equals(false, 'test-value');
    }

    /**
     * Test that the GATE env var validation handles unset gracefully.
     *
     * This is a documentation test for issue #750. The actual gate_check
     * function has an early return for CLI mode, so we test the expected
     * behavior: getenv('GATE') returns false when not set.
     */
    public function testGetenvReturnsFalseWhenNotSet(): void
    {
        // Save and unset GATE env var
        $originalGate = getenv('GATE');
        putenv('GATE');

        $envGate = getenv('GATE');

        // getenv returns false (not empty string) when var is not set
        $this->assertFalse($envGate);
        $this->assertFalse($envGate);

        // Restore GATE env var if it was set
        if ($originalGate !== false) {
            putenv('GATE=' . $originalGate);
        }
    }

    public function testGetenvReturnsEmptyStringWhenSetEmpty(): void
    {
        // Save original and set empty GATE
        $originalGate = getenv('GATE');
        putenv('GATE=');

        $envGate = getenv('GATE');

        // getenv returns empty string when var is set to empty
        $this->assertSame('', $envGate);

        // Restore GATE env var if it was set
        if ($originalGate !== false) {
            putenv('GATE=' . $originalGate);
        }
    }

    public function testSafeFileWriteMethodExists(): void
    {
        $this->assertTrue(function_exists('safe_file_write'));
    }

    public function testSafeFileWriteWithValidPath(): void
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'test');
        $result  = safe_file_write($tmpfile, 'test data');

        $this->assertIsBool($result);

        // Clean up
        if (file_exists($tmpfile)) {
            unlink($tmpfile);
        }
    }

    public function testSafeFileWriteWithInvalidPath(): void
    {
        // Writing to /dev/null/invalid should fail
        $result = @safe_file_write('/dev/null/invalid/path', 'test');
        $this->assertFalse($result);
    }

    public function testAllFunctionsExist(): void
    {
        $expectedFunctions = [
            'clean_error_logs',
            'clean_feed_cache',
            'decode_entities',
            'fetch_error_logs',
            'fetch_feed',
            'fetch_thumbnail',
            'fetch_url',
            'fetch_url_status',
            'gate_check',
            'safe_file_write',
        ];

        foreach ($expectedFunctions as $function) {
            $this->assertTrue(function_exists($function), "Function {$function} does not exist");
        }
    }

    public function testDecodeEntitiesMethodExists(): void
    {
        $this->assertTrue(function_exists('decode_entities'));
    }

    public function testDecodeEntitiesWithPlainText(): void
    {
        $result = decode_entities('Hello World');
        $this->assertSame('Hello World', $result);
    }

    public function testDecodeEntitiesWithSingleEncodedEntities(): void
    {
        // Single-encoded numeric entities should be decoded
        $result = decode_entities('DENNIS ENARSON &#8211; RAMPED');
        $this->assertSame('DENNIS ENARSON – RAMPED', $result);
    }

    public function testDecodeEntitiesWithDoubleEncodedEntities(): void
    {
        // Double-encoded entities (the main bug)
        $result = decode_entities('DENNIS ENARSON &amp;#8211; RAMPED');
        $this->assertSame('DENNIS ENARSON – RAMPED', $result);
    }

    public function testDecodeEntitiesWithTripleEncodedEntities(): void
    {
        // Triple-encoded entities
        $result = decode_entities('Test &amp;amp;#8211; Value');
        $this->assertSame('Test – Value', $result);
    }

    public function testDecodeEntitiesWithCurlyQuotes(): void
    {
        // Double-encoded curly quotes (&#8220; = " and &#8221; = ")
        $result = decode_entities('ODYSSEY &amp;#8220;ON LOCK&amp;#8221; VIDEO');
        $this->assertSame("ODYSSEY \u{201C}ON LOCK\u{201D} VIDEO", $result);
    }

    public function testDecodeEntitiesWithAmpersand(): void
    {
        // Double-encoded ampersand
        $result = decode_entities('Matt Nordstrom &amp;#038; Chad Kerley');
        $this->assertSame('Matt Nordstrom & Chad Kerley', $result);
    }

    public function testDecodeEntitiesWithMixedContent(): void
    {
        // Mix of plain text, regular entities, and double-encoded
        $result = decode_entities('Test &amp;amp; More &amp;#8211; End');
        $this->assertSame('Test & More – End', $result);
    }

    public function testDecodeEntitiesWithEmptyString(): void
    {
        $result = decode_entities('');
        $this->assertSame('', $result);
    }

    public function testDecodeEntitiesPreservesUnicode(): void
    {
        // Should not corrupt existing unicode characters
        $result = decode_entities('Café – Coffee');
        $this->assertSame('Café – Coffee', $result);
    }

    public function testDecodeEntitiesWithNamedEntities(): void
    {
        // Named entities should also be decoded
        $result = decode_entities('Test &amp;amp; &amp;quot;quoted&amp;quot;');
        $this->assertSame('Test & "quoted"', $result);
    }

    public function testFetchUrlPopulatesHttpStatus(): void
    {
        $httpStatus = null;
        // Use an invalid URL that will get a cURL error (no HTTP code)
        fetch_url('http://localhost:1', 'text', 0, $httpStatus);

        // httpStatus should be set (0 for connection failure)
        $this->assertIsInt($httpStatus);
    }

    public function testFetchUrlHttpStatusSetOn404(): void
    {
        $httpStatus = null;
        // The local server returns 404 for this endpoint
        fetch_url($this->localUrl('/status/404'), 'text', 0, $httpStatus);

        $this->assertSame(404, $httpStatus);
    }

    public function testFetchUrl404LogsWarningNotError(): void
    {
        $url    = $this->localUrl('/status/404');
        $result = fetch_url($url, 'text', 0);

        $this->assertFalse($result);
        $this->assertLogged('warning', $url . ' returned 404.');
        $this->assertFalse(TestLogger::didLog('error', $url . ' returned 404.'));
    }

    public function testFetchUrl429ReturnsFalseAndLogsWarning(): void
    {
        // A rate-limited host gets Google's CAPTCHA page with a 429, which
        // must not reach callers that parse the body as a watch page.
        $url        = $this->localUrl('/status/429');
        $httpStatus = null;
        $result     = fetch_url($url, 'text', 0, $httpStatus);

        $this->assertFalse($result);
        $this->assertSame(429, $httpStatus);
        $this->assertLogged('warning', $url . ' returned 429.');
        $this->assertFalse(TestLogger::didLog('error', $url . ' returned 429.'));
    }

    public function testFetchUrlTreatsEveryErrorStatusAsFailedFetch(): void
    {
        // An error page has a body, and it must not reach callers as content
        foreach ([400, 401, 410, 502, 503] as $status) {
            $httpStatus = null;
            $result     = fetch_url($this->localUrl('/error-page/' . $status), 'text', 0, $httpStatus);

            $this->assertFalse($result, 'Status ' . $status . ' returned its error page.');
            $this->assertSame($status, $httpStatus);
        }
    }

    public function testFetchUrlLogsOtherErrorStatusesAtWarning(): void
    {
        $url = $this->localUrl('/error-page/503');
        fetch_url($url, 'text', 0);

        $this->assertLogged('warning', $url . ' returned 503.');
        $this->assertFalse(TestLogger::didLog('error', $url . ' returned 503.'));
    }

    public function testFetchUrlLogs403And500AtError(): void
    {
        foreach ([403, 500] as $status) {
            $url = $this->localUrl('/error-page/' . $status);

            $this->assertFalse(fetch_url($url, 'text', 0));
            $this->assertLogged('error', $url . ' returned ' . $status . '.');
        }
    }

    public function testFetchUrlReturnsBodyForSuccessStatus(): void
    {
        $httpStatus = null;
        $result     = fetch_url($this->localUrl('/body/page'), 'text', 0, $httpStatus);

        $this->assertSame('<html><body>Not an image</body></html>', $result);
        $this->assertSame(200, $httpStatus);
    }

    public function testFetchUrlStatusMethodExists(): void
    {
        $this->assertTrue(function_exists('fetch_url_status'));
    }

    public function testFetchUrlStatusReturnsStatusWithoutFollowingRedirect(): void
    {
        $redirectUrl = null;
        $status      = fetch_url_status($this->localUrl('/redirect/303'), 0, $redirectUrl);

        $this->assertSame(303, $status);
        $this->assertStringEndsWith('/watch?v=test', $redirectUrl);
    }

    public function testFetchUrlStatusReturnsDirectStatus(): void
    {
        $redirectUrl = null;
        $status      = fetch_url_status($this->localUrl('/status/200'), 0, $redirectUrl);

        $this->assertSame(200, $status);
        $this->assertSame('', $redirectUrl);
    }

    public function testFetchUrlStatusReturnsZeroOnTransportFailure(): void
    {
        $redirectUrl = null;
        $status      = fetch_url_status('http://localhost:1', 0, $redirectUrl);

        $this->assertSame(0, $status);
        $this->assertSame('', $redirectUrl);
    }

    public function testFetchThumbnailPassesThroughHttpStatus(): void
    {
        $httpStatus = null;
        // Fetching a non-existent thumbnail should populate httpStatus
        fetch_thumbnail('nonexistent_video', $this->localUrl('/status/404'), $httpStatus);

        $this->assertIsInt($httpStatus);
        $this->assertSame(404, $httpStatus);
    }

    public function testFetchThumbnailStoresWebpThumbnail(): void
    {
        $this->withThumbnailDirectory(function (string $path): void {
            $downloads = $this->downloadFiles();

            $this->assertTrue(fetch_thumbnail('test_video', $this->localUrl('/body/image')));

            $this->assertFileExists($path);
            $this->assertSame(IMAGETYPE_WEBP, getimagesize($path)[2]);
            // The web server reads thumbnails as another user
            $this->assertSame(0644, fileperms($path) & 0777);
            $this->assertSame($downloads, $this->downloadFiles());
        });
    }

    public function testFetchThumbnailLeavesNoFileWhenDownloadIsNotAnImage(): void
    {
        $this->withThumbnailDirectory(function (string $path): void {
            $downloads = $this->downloadFiles();

            $this->assertFalse(fetch_thumbnail('test_video', $this->localUrl('/body/page')));

            // A leftover file would stop the thumbnail from being retried
            $this->assertFileDoesNotExist($path);
            $this->assertSame($downloads, $this->downloadFiles());
        });
    }

    /**
     * Run a test against a temporary thumbnail directory.
     *
     * @param callable(string): void $test Receives the thumbnail path for the video id test_video
     */
    private function withThumbnailDirectory(callable $test): void
    {
        $storageConfig = config('Storage');
        $original      = $storageConfig->thumbnailPath;
        $directory     = sys_get_temp_dir() . '/aggro-thumbs-' . uniqid() . '/';
        mkdir($directory);

        $storageConfig->thumbnailPath = $directory;

        try {
            $test($storageConfig->getThumbnailPath('test_video'));
        } finally {
            $storageConfig->thumbnailPath = $original;
            array_map('unlink', glob($directory . '*'));
            rmdir($directory);
        }
    }

    /**
     * List the thumbnail downloads waiting in the system temporary directory.
     *
     * @return list<string>
     */
    private function downloadFiles(): array
    {
        return glob(sys_get_temp_dir() . '/thumb*');
    }

    public function testFetchUrlHttpStatusBackwardCompatible(): void
    {
        // Calling without the httpStatus param should still work
        $result = fetch_url('http://localhost:1');
        // Should not throw any errors — backward-compatible
        $this->assertFalse($result);
    }

    public function testSafeFileWriteWritesData(): void
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'test');
        $data    = 'Hello, World! 🌍';

        $this->assertTrue(safe_file_write($tmpfile, $data));
        $this->assertSame($data, file_get_contents($tmpfile));

        unlink($tmpfile);
    }
}
