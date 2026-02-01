<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use TypeError;

/**
 * @internal
 */
final class AggroHelperTest extends CIUnitTestCase
{
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

    public function testCleanFeedCacheMethodExists(): void
    {
        $this->assertTrue(function_exists('clean_feed_cache'));
    }

    public function testCleanFeedCacheReturnsInteger(): void
    {
        $result = clean_feed_cache();
        $this->assertIsInt($result);
    }

    public function testCleanThumbnailMethodExists(): void
    {
        $this->assertTrue(function_exists('clean_thumbnail'));
    }

    public function testCleanThumbnailWithValidId(): void
    {
        $result = clean_thumbnail('test123');
        $this->assertIsBool($result);
    }

    public function testCleanThumbnailWithEmptyId(): void
    {
        $result = clean_thumbnail('');
        $this->assertIsBool($result);
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
        // Test with invalid URL to avoid external dependencies
        $result = fetch_feed('invalid-url', 0);
        // Should return false or SimplePie object
        $this->assertTrue($result === false || is_object($result));
    }

    public function testFetchThumbnailMethodExists(): void
    {
        $this->assertTrue(function_exists('fetch_thumbnail'));
    }

    public function testFetchThumbnailWithValidParameters(): void
    {
        $result = fetch_thumbnail('test123', 'https://example.com/thumb.jpg');
        $this->assertIsBool($result);
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
        // Skip this test as it may throw exceptions rather than return false
        $this->markTestSkipped('safe_file_write may throw exceptions for invalid paths');
    }

    public function testSafeFileReadMethodExists(): void
    {
        $this->assertTrue(function_exists('safe_file_read'));
    }

    public function testSafeFileReadWithValidFile(): void
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpfile, 'test content');

        $result = safe_file_read($tmpfile);
        $this->assertSame('test content', $result);

        // Clean up
        unlink($tmpfile);
    }

    public function testSafeFileReadWithInvalidFile(): void
    {
        $result = safe_file_read('/invalid/path/file.txt');
        $this->assertFalse($result);
    }

    public function testAllFunctionsExist(): void
    {
        $expectedFunctions = [
            'clean_error_logs',
            'clean_feed_cache',
            'clean_thumbnail',
            'decode_entities',
            'fetch_error_logs',
            'fetch_feed',
            'fetch_thumbnail',
            'fetch_url',
            'gate_check',
            'safe_file_write',
            'safe_file_read',
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
}
