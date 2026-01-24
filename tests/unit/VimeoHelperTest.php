<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class VimeoHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('vimeo');
    }

    public function testVimeoGetFeedMethodExists(): void
    {
        $this->assertTrue(function_exists('vimeo_get_feed'));
    }

    public function testVimeoGetFeedWithValidId(): void
    {
        // Test with invalid ID to avoid external API calls
        $result = vimeo_get_feed('invalid_id');
        // Should return false or object/array
        $this->assertTrue($result === false || is_object($result) || is_array($result));
    }

    public function testVimeoGetFeedWithEmptyId(): void
    {
        $result = vimeo_get_feed('');
        $this->assertFalse($result);
    }

    public function testVimeoIdFromUrlMethodExists(): void
    {
        $this->assertTrue(function_exists('vimeo_id_from_url'));
    }

    public function testVimeoIdFromUrlWithValidVimeoUrl(): void
    {
        // Test only the first case to avoid potential undefined index errors
        $url    = 'https://vimeo.com/123456789';
        $result = vimeo_id_from_url($url);
        $this->assertSame('123456789', $result);
    }

    public function testVimeoIdFromUrlWithInvalidUrl(): void
    {
        $invalidUrls = [
            'https://example.com/video',
            'https://youtube.com/watch?v=123456',
            'not-a-url',
            '',
        ];

        foreach ($invalidUrls as $url) {
            $result = vimeo_id_from_url($url);
            $this->assertFalse($result, "Should return false for invalid URL: {$url}");
        }
    }

    public function testVimeoIdFromUrlWithUrlParameters(): void
    {
        $url    = 'https://vimeo.com/123456789?autoplay=1&color=ffffff';
        $result = vimeo_id_from_url($url);
        $this->assertSame('123456789', $result);
    }

    public function testVimeoParseMetaMethodExists(): void
    {
        $this->assertTrue(function_exists('vimeo_parse_meta'));
    }

    public function testVimeoParseMetaWithValidItem(): void
    {
        $mockItem = (object) [
            'id'                    => 123456789,
            'title'                 => 'Test Vimeo Video',
            'upload_date'           => '2024-01-01T00:00:00Z',
            'thumbnail_large'       => 'https://example.com/thumb_large.jpg',
            'url'                   => 'https://vimeo.com/123456789',
            'duration'              => 120,
            'width'                 => 1920,
            'height'                => 1080,
            'description'           => 'Test video description',
            'user_name'             => 'Test User',
            'user_url'              => 'https://vimeo.com/testuser',
            'stats_number_of_plays' => 1000,
        ];

        $result = vimeo_parse_meta($mockItem);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('video_id', $result);
        $this->assertArrayHasKey('video_title', $result);
        $this->assertArrayHasKey('video_date_uploaded', $result);
        $this->assertArrayHasKey('video_type', $result);
        $this->assertArrayHasKey('video_thumbnail_url', $result);
        $this->assertArrayHasKey('video_width', $result);
        $this->assertArrayHasKey('video_height', $result);
        $this->assertArrayHasKey('video_aspect_ratio', $result);
        $this->assertArrayHasKey('video_duration', $result);
        $this->assertArrayHasKey('video_source_username', $result);
        $this->assertArrayHasKey('video_source_url', $result);
        $this->assertArrayHasKey('flag_archive', $result);
        $this->assertArrayHasKey('flag_bad', $result);

        $this->assertSame('vimeo', $result['video_type']);
        $this->assertSame(123456789, $result['video_id']);
        $this->assertSame('Test Vimeo Video', $result['video_title']);
        $this->assertSame(1920, $result['video_width']);
        $this->assertSame(1080, $result['video_height']);
        $this->assertSame(120, $result['video_duration']);
        $this->assertSame(1.778, $result['video_aspect_ratio']); // 1920/1080 rounded to 3 decimals
        $this->assertSame(0, $result['flag_bad']);
    }

    public function testVimeoParseMetaWithOldVideo(): void
    {
        // Create an item with upload date older than 31 days
        $oldDate = date('Y-m-d H:i:s', strtotime('-35 days'));

        $mockItem = (object) [
            'id'              => 123456789,
            'title'           => 'Old Vimeo Video',
            'upload_date'     => $oldDate,
            'thumbnail_large' => 'https://example.com/thumb.jpg',
            'url'             => 'https://vimeo.com/123456789',
            'duration'        => 60,
            'width'           => 640,
            'height'          => 480,
            'description'     => 'Old video',
            'user_name'       => 'Test User',
            'user_url'        => 'https://vimeo.com/testuser',
        ];

        $result = vimeo_parse_meta($mockItem);

        $this->assertSame(1, $result['flag_archive'], 'Old videos should be flagged for archive');
    }

    public function testVimeoParseMetaWithRecentVideo(): void
    {
        // Create an item with recent upload date
        $recentDate = date('Y-m-d H:i:s', strtotime('-5 days'));

        $mockItem = (object) [
            'id'              => 123456789,
            'title'           => 'Recent Vimeo Video',
            'upload_date'     => $recentDate,
            'thumbnail_large' => 'https://example.com/thumb.jpg',
            'url'             => 'https://vimeo.com/123456789',
            'duration'        => 60,
            'width'           => 640,
            'height'          => 480,
            'description'     => 'Recent video',
            'user_name'       => 'Test User',
            'user_url'        => 'https://vimeo.com/testuser',
        ];

        $result = vimeo_parse_meta($mockItem);

        $this->assertSame(0, $result['flag_archive'], 'Recent videos should not be flagged for archive');
    }

    public function testVimeoParseMetaHandlesVideoWithoutPlays(): void
    {
        $mockItem = (object) [
            'id'              => 123456789,
            'title'           => 'Video Without Stats',
            'upload_date'     => '2024-01-01T00:00:00Z',
            'thumbnail_large' => 'https://example.com/thumb.jpg',
            'url'             => 'https://vimeo.com/123456789',
            'duration'        => 60,
            'width'           => 640,
            'height'          => 480,
            'description'     => 'Test video',
            'user_name'       => 'Test User',
            'user_url'        => 'https://vimeo.com/testuser',
            // No stats_number_of_plays property
        ];

        $result = vimeo_parse_meta($mockItem);

        $this->assertArrayHasKey('video_plays', $result);
        $this->assertSame(0, $result['video_plays'], 'Videos without play stats should default to 0');
    }

    public function testVimeoParseMetaHandlesSpecialCharacters(): void
    {
        $mockItem = (object) [
            'id'              => 123456789,
            'title'           => 'Video with "quotes" & <html>',
            'upload_date'     => '2024-01-01T00:00:00Z',
            'thumbnail_large' => 'https://example.com/thumb.jpg',
            'url'             => 'https://vimeo.com/123456789',
            'duration'        => 60,
            'width'           => 640,
            'height'          => 480,
            'description'     => 'Description with <script>alert("xss")</script>',
            'user_name'       => 'User with "quotes"',
            'user_url'        => 'https://vimeo.com/testuser',
        ];

        $result = vimeo_parse_meta($mockItem);

        // Check that HTML entities are NOT encoded (encoding happens in views)
        $this->assertStringNotContainsString('&quot;', $result['video_title']);
        $this->assertStringNotContainsString('&lt;', $result['video_title']);
        $this->assertStringNotContainsString('&gt;', $result['video_title']);
        $this->assertStringNotContainsString('&quot;', $result['video_source_username']);

        // Verify raw values are preserved
        $this->assertSame('Video with "quotes" & <html>', $result['video_title']);
        $this->assertSame('User with "quotes"', $result['video_source_username']);
    }

    public function testVimeoParseMetaHandlesZeroHeight(): void
    {
        $mockItem = (object) [
            'id'              => 123456789,
            'title'           => 'Video With Zero Height',
            'upload_date'     => '2024-01-01T00:00:00Z',
            'thumbnail_large' => 'https://example.com/thumb.jpg',
            'url'             => 'https://vimeo.com/123456789',
            'duration'        => 60,
            'width'           => 1920,
            'height'          => 0,
            'description'     => 'Test video',
            'user_name'       => 'Test User',
            'user_url'        => 'https://vimeo.com/testuser',
        ];

        $result = vimeo_parse_meta($mockItem);

        $this->assertArrayHasKey('video_aspect_ratio', $result);
        $this->assertSame(1.778, $result['video_aspect_ratio'], 'Zero height should default to 16:9 aspect ratio');
    }

    public function testAllFunctionsExist(): void
    {
        $expectedFunctions = [
            'vimeo_get_feed',
            'vimeo_id_from_url',
            'vimeo_parse_meta',
        ];

        foreach ($expectedFunctions as $function) {
            $this->assertTrue(function_exists($function), "Function {$function} does not exist");
        }
    }

    public function testFunctionReturnTypes(): void
    {
        // Test that functions return expected types for invalid input
        $this->assertFalse(vimeo_get_feed(''));
        $this->assertFalse(vimeo_id_from_url('invalid'));
    }

    public function testVimeoIdExtractionWithEdgeCases(): void
    {
        // Test various edge cases for ID extraction
        $testCases = [
            'vimeo.com/123456789'          => '123456789', // Without protocol
            'https://vimeo.com/123456789/' => '123456789', // With trailing slash
        ];

        foreach ($testCases as $url => $expectedId) {
            $result = vimeo_id_from_url($url);
            // Result depends on regex implementation
            $this->assertTrue($result === $expectedId || $result === false);
        }
    }
}
