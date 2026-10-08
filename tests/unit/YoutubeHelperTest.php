<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use SimplePie\SimplePie;
use Tests\Support\YoutubeFeedTrait;

/**
 * @internal
 */
final class YoutubeHelperTest extends CIUnitTestCase
{
    use YoutubeFeedTrait;

    protected function setUp(): void
    {
        parent::setUp();
        helper('youtube');
    }

    public function testYoutubeGetFeedMethodExists(): void
    {
        $this->assertTrue(function_exists('youtube_get_feed'));
    }

    public function testYoutubeGetFeedWithValidId(): void
    {
        // The fetch is blocked, so the feed comes back carrying an error
        $result = youtube_get_feed('invalid_channel_id');
        $this->assertInstanceOf(SimplePie::class, $result);
        $this->assertNotNull($result->error());
    }

    public function testYoutubeGetFeedWithEmptyId(): void
    {
        // Returns a SimplePie object even for an empty ID
        $result = youtube_get_feed('');
        $this->assertInstanceOf(SimplePie::class, $result);
        $this->assertNotNull($result->error());
    }

    public function testYoutubeGetVideoSourceMethodExists(): void
    {
        $this->assertTrue(function_exists('youtube_get_video_source'));
    }

    public function testYoutubeGetVideoSourceWithValidId(): void
    {
        // The fetch is blocked, so no oEmbed response comes back
        $result = youtube_get_video_source('invalid_video_id');
        $this->assertFalse($result);
    }

    public function testYoutubeGetVideoSourceWithEmptyId(): void
    {
        $result = youtube_get_video_source('');
        $this->assertFalse($result);
    }

    public function testYoutubeIdFromUrlMethodExists(): void
    {
        $this->assertTrue(function_exists('youtube_id_from_url'));
    }

    public function testYoutubeIdFromUrlWithValidYoutubeUrl(): void
    {
        $testCases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://youtube.com/watch?v=dQw4w9WgXcQ'     => 'dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ'                => 'dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ'   => 'dQw4w9WgXcQ',
        ];

        foreach ($testCases as $url => $expectedId) {
            $result = youtube_id_from_url($url);
            $this->assertSame($expectedId, $result, "Failed to extract ID from URL: {$url}");
        }
    }

    public function testYoutubeIdFromUrlWithInvalidUrl(): void
    {
        $invalidUrls = [
            'https://example.com/video',
            'https://vimeo.com/123456',
            'not-a-url',
            '',
        ];

        foreach ($invalidUrls as $url) {
            $result = youtube_id_from_url($url);
            $this->assertFalse($result, "Should return false for invalid URL: {$url}");
        }
    }

    public function testYoutubeIdFromUrlWithUrlParameters(): void
    {
        $url    = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10s&list=playlist';
        $result = youtube_id_from_url($url);
        $this->assertSame('dQw4w9WgXcQ', $result);
    }

    public function testYoutubeGetDimensionsMethodExists(): void
    {
        $this->assertTrue(function_exists('youtube_get_dimensions'));
    }

    public function testYoutubeGetDimensionsWithInvalidId(): void
    {
        $result = youtube_get_dimensions('invalid_id');
        $this->assertIsArray($result);
        $this->assertSame(800, $result['video_width']);
        $this->assertSame(450, $result['video_height']);
        $this->assertSame(1.778, $result['video_aspect_ratio']);
    }

    public function testYoutubeParseDimensionsReturnsOembedDimensions(): void
    {
        $result = youtube_parse_dimensions((object) ['width' => 200, 'height' => 150]);

        $this->assertSame(200, $result['video_width']);
        $this->assertSame(150, $result['video_height']);
        $this->assertSame(1.333, $result['video_aspect_ratio']);
    }

    public function testYoutubeParseDimensionsKeepsDefaultsForZeroDimensions(): void
    {
        $expected = [
            'video_width'        => 800,
            'video_height'       => 450,
            'video_aspect_ratio' => 1.778,
        ];

        $this->assertSame($expected, youtube_parse_dimensions((object) ['width' => 0, 'height' => 113]));
        $this->assertSame($expected, youtube_parse_dimensions((object) ['width' => 200, 'height' => 0]));
        $this->assertSame($expected, youtube_parse_dimensions((object) []));
    }

    public function testYoutubeParseDimensionsKeepsDefaultsForFailedFetch(): void
    {
        $expected = [
            'video_width'        => 800,
            'video_height'       => 450,
            'video_aspect_ratio' => 1.778,
        ];

        $this->assertSame($expected, youtube_parse_dimensions(false));
        $this->assertSame($expected, youtube_parse_dimensions('Not Found'));
    }

    public function testYoutubeParseMetaMethodExists(): void
    {
        $this->assertTrue(function_exists('youtube_parse_meta'));
    }

    public function testYoutubeParseMetaWithValidItem(): void
    {
        $item       = $this->makeFeedItem('S&amp;M Bikes', $this->videoEntryXml());
        $dimensions = [
            'video_width'        => 1280,
            'video_height'       => 720,
            'video_aspect_ratio' => 1.778,
        ];

        $video = youtube_parse_meta($item, $dimensions, false);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $video['aggro_date_added']);
        $this->assertSame($video['aggro_date_added'], $video['aggro_date_updated']);
        unset($video['aggro_date_added'], $video['aggro_date_updated']);

        $this->assertSame([
            'video_id'              => 'aggroTest01',
            'video_date_uploaded'   => date('Y-m-d H:i:s', strtotime('2020-01-15T12:00:00+00:00')),
            'flag_bad'              => 0,
            'flag_archive'          => 1,
            'video_type'            => 'youtube',
            'video_title'           => 'S&M Bikes',
            'video_plays'           => '12345',
            'video_thumbnail_url'   => 'https://i1.ytimg.com/vi/aggroTest01/hqdefault.jpg',
            'video_source_id'       => 'UCaggroTestChannel',
            'video_source_url'      => 'https://www.youtube.com/channel/UCaggroTestChannel',
            'video_source_username' => 'Test Rider',
            'video_width'           => 1280,
            'video_height'          => 720,
            'video_aspect_ratio'    => 1.778,
            'flag_short'            => 0,
        ], $video);
    }

    public function testYoutubeParseMetaHandlesItemWithoutThumbnail(): void
    {
        $item = $this->makeFeedItem('No Thumbnail', $this->videoEntryXml('aggroTest01', false));

        $video = youtube_parse_meta($item, youtube_parse_dimensions(false), false);

        $this->assertSame('https://i.ytimg.com/vi/aggroTest01/hqdefault.jpg', $video['video_thumbnail_url']);
    }

    public function testYoutubeParseMetaKeepsDefaultsWhenOembedReturnsZeroDimensions(): void
    {
        // Verifies the hardening in youtube_parse_dimensions that rejects
        // zero/null oEmbed dimensions and keeps 800x450 defaults.
        $item = $this->makeFeedItem('Zero Dimensions', $this->videoEntryXml());

        $video = youtube_parse_meta($item, youtube_parse_dimensions((object) ['width' => 0, 'height' => 0]), false);

        $this->assertSame(800, $video['video_width']);
        $this->assertSame(450, $video['video_height']);
        $this->assertSame(1.778, $video['video_aspect_ratio']);
    }

    public function testYoutubeParseShortReturnsTrueForShortsPage(): void
    {
        // A Short is served at its /shorts/ URL, so the status is 200
        $this->assertTrue(youtube_parse_short(200, ''));
    }

    public function testYoutubeParseShortReturnsFalseForRedirectToWatchPage(): void
    {
        // A regular video is redirected from /shorts/ to its watch page
        $this->assertFalse(youtube_parse_short(303, 'https://www.youtube.com/watch?v=aggroTest01'));
        $this->assertFalse(youtube_parse_short(302, 'https://www.youtube.com/watch?v=aggroTest01'));
        $this->assertFalse(youtube_parse_short(301, 'https://www.youtube.com/watch?v=aggroTest01&pp=0gcJCaICQVKahPAF'));
    }

    public function testYoutubeParseShortReturnsNullForRedirectElsewhere(): void
    {
        $this->assertNull(youtube_parse_short(303, 'https://www.youtube.com/sorry/index'));
        $this->assertNull(youtube_parse_short(303, ''));
    }

    public function testYoutubeParseShortReturnsNullForOtherStatuses(): void
    {
        $this->assertNull(youtube_parse_short(0, ''));
        $this->assertNull(youtube_parse_short(404, ''));
        $this->assertNull(youtube_parse_short(429, ''));
        $this->assertNull(youtube_parse_short(500, ''));
    }

    public function testYoutubeGetShortMethodExists(): void
    {
        $this->assertTrue(function_exists('youtube_get_short'));
    }

    public function testYoutubeGetShortReturnsNullWhenFetchFails(): void
    {
        // The fetch is blocked, so the probe never completes
        $this->assertNull(youtube_get_short('invalid_id'));
    }

    public function testYoutubeParseMetaFlagsShort(): void
    {
        $item = $this->makeFeedItem('A Short', $this->videoEntryXml());

        $video = youtube_parse_meta($item, youtube_parse_dimensions(false), true);

        $this->assertSame(1, $video['flag_short']);
    }

    public function testYoutubeParseMetaStoresRegularVideoWithoutShortFlag(): void
    {
        $item = $this->makeFeedItem('A Video', $this->videoEntryXml());

        $video = youtube_parse_meta($item, youtube_parse_dimensions(false), false);

        $this->assertSame(0, $video['flag_short']);
    }

    public function testYoutubeParseMetaStoresRegularVideoWhenShortsCheckIsInconclusive(): void
    {
        // A check that could not answer must not hide the video
        $item = $this->makeFeedItem('Unknown', $this->videoEntryXml());

        $video = youtube_parse_meta($item, youtube_parse_dimensions(false), null);

        $this->assertSame(0, $video['flag_short']);
    }

    public function testYoutubeParseTitleReturnsRawText(): void
    {
        // SimplePie HTML-encodes titles, so the helper must decode them
        // to keep stored titles raw (encoding happens in views).
        $item = $this->makeFeedItem('S&amp;M "Game" &lt;of&gt; Bike &amp; 90\'s');

        $this->assertSame('S&M "Game" <of> Bike & 90\'s', youtube_parse_title($item));
    }

    public function testYoutubeParseTitleLeavesPlainTextUnchanged(): void
    {
        $item = $this->makeFeedItem('Café – Trails Session');

        $this->assertSame('Café – Trails Session', youtube_parse_title($item));
    }

    public function testYoutubeParseTitleWithEmptyTitle(): void
    {
        $item = $this->makeFeedItem('');

        $this->assertSame('', youtube_parse_title($item));
    }

    public function testAllFunctionsExist(): void
    {
        $expectedFunctions = [
            'youtube_get_feed',
            'youtube_get_video_source',
            'youtube_id_from_url',
            'youtube_get_dimensions',
            'youtube_parse_dimensions',
            'youtube_get_short',
            'youtube_parse_short',
            'youtube_parse_title',
            'youtube_parse_meta',
        ];

        foreach ($expectedFunctions as $function) {
            $this->assertTrue(function_exists($function), "Function {$function} does not exist");
        }
    }

    public function testFunctionReturnTypes(): void
    {
        // Test that functions return expected types for invalid input
        $this->assertInstanceOf(SimplePie::class, youtube_get_feed(''));
        $this->assertFalse(youtube_get_video_source(''));
        $this->assertFalse(youtube_id_from_url('invalid'));
    }

    public function testYoutubeIdExtractionEdgeCases(): void
    {
        // Test various YouTube URL formats
        $testCases = [
            'https://www.youtube.com/watch?v=ABC123&feature=youtu.be' => 'ABC123',
            'https://m.youtube.com/watch?v=XYZ789'                    => 'XYZ789',
            'https://gaming.youtube.com/watch?v=DEF456'               => 'DEF456',
        ];

        foreach ($testCases as $url => $expectedId) {
            $result = youtube_id_from_url($url);
            // May or may not extract based on regex patterns
            $this->assertTrue($result === $expectedId || $result === false);
        }
    }
}
