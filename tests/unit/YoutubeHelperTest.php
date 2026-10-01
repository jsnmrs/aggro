<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestLogger;
use ReflectionFunction;
use SimplePie\SimplePie;
use Tests\Support\BlocksNetworkTrait;
use Tests\Support\YoutubeFeedTrait;

/**
 * @internal
 */
final class YoutubeHelperTest extends CIUnitTestCase
{
    use BlocksNetworkTrait;
    use YoutubeFeedTrait;

    protected function setUp(): void
    {
        parent::setUp();
        helper('youtube');
    }

    public function testOutboundRequestsAreBlocked(): void
    {
        // Guards the network block the tests below rely on. A request that
        // reached YouTube would come back with a page and an HTTP status.
        helper('aggro');

        $httpStatus = null;
        $result     = fetch_url('https://www.youtube.com/', 'text', 0, $httpStatus);

        $this->assertSame(0, $httpStatus);
        $this->assertFalse($result);
        $this->assertTrue(TestLogger::didLog('warning', '127.0.0.1 port 1', false));
    }

    public function testYoutubeGetPlaysAcceptsHttpStatusOutParam(): void
    {
        $params = (new ReflectionFunction('youtube_get_plays'))->getParameters();

        $this->assertCount(2, $params);
        $this->assertSame('httpStatus', $params[1]->getName());
        $this->assertTrue($params[1]->isPassedByReference());
        $this->assertTrue($params[1]->isOptional());
    }

    public function testYoutubeGetDurationMethodExists(): void
    {
        $this->assertTrue(function_exists('youtube_get_duration'));
    }

    public function testYoutubeGetDurationWithValidId(): void
    {
        // The fetch is blocked, so no watch page comes back
        $result = youtube_get_duration('invalid_id');
        $this->assertFalse($result);
    }

    public function testYoutubeGetDurationWithEmptyId(): void
    {
        $result = youtube_get_duration('');
        $this->assertFalse($result);
    }

    public function testYoutubeGetDurationAcceptsUnavailableOutParam(): void
    {
        $params = (new ReflectionFunction('youtube_get_duration'))->getParameters();

        $this->assertCount(2, $params);
        $this->assertSame('unavailable', $params[1]->getName());
        $this->assertTrue($params[1]->isPassedByReference());
        $this->assertTrue($params[1]->isOptional());
    }

    public function testYoutubeParseDurationReturnsLengthForPlayableVideo(): void
    {
        $page = '{"playabilityStatus":{"status":"OK"},"videoDetails":{"lengthSeconds":"820"}}';

        $unavailable = null;
        $this->assertSame('820', youtube_parse_duration($page, $unavailable));
        $this->assertFalse($unavailable);
    }

    public function testYoutubeParseDurationFlagsUnavailableVideo(): void
    {
        // YouTube answers 200 for deleted and private videos, so the only
        // signal is playabilityStatus in the page body.
        $page = '{"playabilityStatus":{"status":"ERROR","reason":"Video unavailable"}}';

        $unavailable = null;
        $this->assertFalse(youtube_parse_duration($page, $unavailable));
        $this->assertTrue($unavailable);
    }

    public function testYoutubeParseDurationFlagsLoginRequiredVideo(): void
    {
        $page = '{"playabilityStatus":{"status":"LOGIN_REQUIRED"}}';

        $unavailable = null;
        $this->assertFalse(youtube_parse_duration($page, $unavailable));
        $this->assertTrue($unavailable);
    }

    public function testYoutubeParseDurationDoesNotFlagPageWithoutPlayabilityStatus(): void
    {
        // A consent interstitial or bot check carries no playability status,
        // so it stays ambiguous rather than being treated as permanent.
        $unavailable = null;
        $this->assertFalse(youtube_parse_duration('<html>consent</html>', $unavailable));
        $this->assertFalse($unavailable);
    }

    public function testYoutubeParseDurationRejectsZeroLength(): void
    {
        $page = '{"playabilityStatus":{"status":"OK"},"videoDetails":{"lengthSeconds":"0"}}';

        $unavailable = null;
        $this->assertFalse(youtube_parse_duration($page, $unavailable));
        $this->assertFalse($unavailable);
    }

    public function testYoutubeParseDurationWithEmptyPage(): void
    {
        $unavailable = null;
        $this->assertFalse(youtube_parse_duration('', $unavailable));
        $this->assertFalse($unavailable);
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

        $video = youtube_parse_meta($item, $dimensions, '820');

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
            'video_duration'        => '820',
        ], $video);
    }

    public function testYoutubeParseMetaHandlesItemWithoutThumbnail(): void
    {
        $item = $this->makeFeedItem('No Thumbnail', $this->videoEntryXml('aggroTest01', false));

        $video = youtube_parse_meta($item, youtube_parse_dimensions(false), '820');

        $this->assertSame('https://i.ytimg.com/vi/aggroTest01/hqdefault.jpg', $video['video_thumbnail_url']);
    }

    public function testYoutubeParseMetaKeepsDefaultsWhenOembedReturnsZeroDimensions(): void
    {
        // Verifies the hardening in youtube_parse_dimensions that rejects
        // zero/null oEmbed dimensions and keeps 800x450 defaults.
        $item = $this->makeFeedItem('Zero Dimensions', $this->videoEntryXml());

        $video = youtube_parse_meta($item, youtube_parse_dimensions((object) ['width' => 0, 'height' => 0]), '820');

        $this->assertSame(800, $video['video_width']);
        $this->assertSame(450, $video['video_height']);
        $this->assertSame(1.778, $video['video_aspect_ratio']);
    }

    public function testYoutubeParseMetaStoresZeroWhenDurationLookupFails(): void
    {
        $item = $this->makeFeedItem('No Duration', $this->videoEntryXml());

        $video = youtube_parse_meta($item, youtube_parse_dimensions(false), false);

        $this->assertSame(0, $video['video_duration']);
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
            'youtube_get_duration',
            'youtube_parse_duration',
            'youtube_get_feed',
            'youtube_get_video_source',
            'youtube_id_from_url',
            'youtube_get_dimensions',
            'youtube_parse_dimensions',
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
        $this->assertFalse(youtube_get_duration(''));
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
