<?php

namespace Tests\Unit;

use App\Models\AggroModels;
use App\Models\UtilityModels;
use App\Models\YoutubeModels;
use CodeIgniter\Model;
use Config\Storage;
use ReflectionClass;
use SimplePie\SimplePie;
use Tests\Support\DatabaseTestCase;
use Tests\Support\YoutubeFeedTrait;

/**
 * @internal
 */
final class YoutubeModelsTest extends DatabaseTestCase
{
    use YoutubeFeedTrait;

    protected YoutubeModels $model;
    private Storage $storageConfig;
    private int $originalDelay;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageConfig                    = config('Storage');
        $this->originalDelay                    = $this->storageConfig->shortRequestDelay;
        $this->storageConfig->shortRequestDelay = 0;

        $this->model = new YoutubeModels();
    }

    protected function tearDown(): void
    {
        $this->storageConfig->shortRequestDelay = $this->originalDelay;
        parent::tearDown();
    }

    /**
     * Build a YoutubeModels whose fetchShort() returns canned results
     * keyed by video_id instead of probing YouTube.
     *
     * Each probe and each pause is counted so tests can assert on the
     * request budget without spending wall-clock time.
     *
     * @param UtilityModels|null $utilityModel Optional utility model override
     * @param AggroModels|null   $aggroModel   Optional aggro model override
     */
    private function buildModelWithCannedShorts(?UtilityModels $utilityModel = null, ?AggroModels $aggroModel = null): YoutubeModels
    {
        $utilityModel ??= $this->createMock(UtilityModels::class);

        return new class ($aggroModel, $utilityModel) extends YoutubeModels {
            /**
             * @var array<string, bool|null>
             */
            public array $shorts = [];

            /**
             * Per-video Shorts results consumed in order, ahead of $shorts.
             *
             * @var array<string, list<bool|null>>
             */
            public array $shortSequence = [];

            /**
             * @var array<string, int>
             */
            public array $shortCalls = [];

            public int $sleepCalls = 0;

            protected function fetchShort($videoId): ?bool
            {
                $this->shortCalls[$videoId] = ($this->shortCalls[$videoId] ?? 0) + 1;

                if (! empty($this->shortSequence[$videoId])) {
                    return array_shift($this->shortSequence[$videoId]);
                }

                return $this->shorts[$videoId] ?? false;
            }

            protected function sleepBetweenFetches(): void
            {
                $this->sleepCalls++;
            }
        };
    }

    /**
     * Build an AggroModels mock that treats every video as new and
     * captures each row passed to addVideo().
     *
     * @param array $added Receives the added rows
     */
    private function buildAggroCapturingAdds(array &$added): AggroModels
    {
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(false);
        $mockAggro->method('addVideo')->willReturnCallback(static function ($video) use (&$added) {
            $added[] = $video;

            return true;
        });

        return $mockAggro;
    }

    public function testModelExtendsCodeIgniterModel(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testConstructorAcceptsDependencyInjection(): void
    {
        $mockAggro   = $this->createMock(AggroModels::class);
        $mockUtility = $this->createMock(UtilityModels::class);

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $reflection = new ReflectionClass($model);

        $aggroProp = $reflection->getProperty('aggroModel');
        $this->assertSame($mockAggro, $aggroProp->getValue($model));

        $utilityProp = $reflection->getProperty('utilityModel');
        $this->assertSame($mockUtility, $utilityProp->getValue($model));
    }

    public function testConstructorCreatesDefaultDependencies(): void
    {
        $model = new YoutubeModels();

        $reflection = new ReflectionClass($model);

        $aggroProp = $reflection->getProperty('aggroModel');
        $this->assertInstanceOf(AggroModels::class, $aggroProp->getValue($model));

        $utilityProp = $reflection->getProperty('utilityModel');
        $this->assertInstanceOf(UtilityModels::class, $utilityProp->getValue($model));
    }

    public function testSearchChannelMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'searchChannel'));
    }

    public function testSearchChannelWithNullFeed(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->searchChannel($mockFeed, 'test123');
        $this->assertFalse($result);
    }

    public function testParseChannelMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'parseChannel'));
    }

    public function testParseChannelWithEmptyFeed(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testSearchChannelHandlesVideoNotFound(): void
    {
        $mockItem = new class () {
            public function get_item_tags($namespace, $tag): array
            {
                return [['data' => 'different_video_id']];
            }
        };

        $mockFeed = new class ($mockItem) {
            private $item;

            public function __construct($item)
            {
                $this->item = $item;
            }

            public function get_items($start = 0, $end = 0): array
            {
                return [$this->item];
            }
        };

        $result = $this->model->searchChannel($mockFeed, 'target_video_id');
        $this->assertFalse($result);
    }

    public function testParseChannelCalculatesCorrectAddCount(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testSearchChannelReturnsBoolean(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->searchChannel($mockFeed, 'test123');
        $this->assertIsBool($result);
    }

    public function testParseChannelReturnsInteger(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertIsInt($result);
    }

    public function testSearchChannelWithValidVideoId(): void
    {
        // The dimension lookup is blocked, so the video is added with the
        // 800x450 defaults.
        $added = [];

        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(false);
        $mockAggro->expects($this->once())
            ->method('addVideo')
            ->willReturnCallback(static function ($video) use (&$added) {
                $added[] = $video;

                return true;
            });

        $model = new YoutubeModels($mockAggro, $this->createMock(UtilityModels::class));

        $feed = $this->makeFeed(
            '<title>Other Video</title>' . $this->videoEntryXml('otherVideo1'),
            '<title>Target Video</title>' . $this->videoEntryXml('targetVideo'),
        );

        $result = $model->searchChannel($feed, 'targetVideo');

        $this->assertTrue($result);
        $this->assertSame('targetVideo', $added[0]['video_id']);
        $this->assertSame('Target Video', $added[0]['video_title']);
        $this->assertSame(800, $added[0]['video_width']);
        $this->assertSame(450, $added[0]['video_height']);
    }

    public function testSearchChannelWithExistingVideo(): void
    {
        // Mock AggroModels to return true for checkVideo (video already exists)
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(true);

        $mockUtility = $this->createMock(UtilityModels::class);

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $mockItem = new class () {
            public function get_item_tags($namespace, $tag): array
            {
                return [['data' => 'existing_video_id']];
            }
        };

        $mockFeed = new class ($mockItem) {
            private $item;

            public function __construct($item)
            {
                $this->item = $item;
            }

            public function get_items($start = 0, $end = 0): array
            {
                return [$this->item];
            }
        };

        // Video exists, so searchChannel should return false (not added)
        $result = $model->searchChannel($mockFeed, 'existing_video_id');
        $this->assertFalse($result);
    }

    public function testParseChannelWithMultipleNewVideos(): void
    {
        $addedIds = [];

        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(false);
        $mockAggro->expects($this->exactly(2))
            ->method('addVideo')
            ->willReturnCallback(static function ($video) use (&$addedIds) {
                $addedIds[] = $video['video_id'];

                return true;
            });
        $mockAggro->expects($this->never())->method('setVideoPlays');

        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->expects($this->once())
            ->method('sendLog')
            ->with('Ran YouTube fetch. Added 2 new-to-me videos.');

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $feed = $this->makeFeed(
            '<title>First Video</title>' . $this->videoEntryXml('firstVideo1'),
            '<title>Second Video</title>' . $this->videoEntryXml('secondVideo'),
        );

        $result = $model->parseChannel($feed);

        $this->assertSame(2, $result);
        $this->assertEqualsCanonicalizing(['firstVideo1', 'secondVideo'], $addedIds);
    }

    public function testParseChannelDoesNotLogForZeroVideos(): void
    {
        // Mock dependencies - no videos added so sendLog should not be called
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(true); // All videos exist

        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->expects($this->never())->method('sendLog');

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testParseChannelUpdatesPlaysForExistingVideo(): void
    {
        // Existing videos get their play counts refreshed from the feed
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(true);
        $mockAggro->expects($this->once())
            ->method('setVideoPlays')
            ->with('existing_video_id', 12345);

        $mockUtility = $this->createMock(UtilityModels::class);

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $mockItem = new class () {
            public function get_item_tags($namespace, $tag): array
            {
                if ($tag === 'videoId') {
                    return [['data' => 'existing_video_id']];
                }

                // media:group carrying media:community > media:statistics views
                return [[
                    'child' => [
                        SimplePie::NAMESPACE_MEDIARSS => [
                            'community' => [[
                                'child' => [
                                    SimplePie::NAMESPACE_MEDIARSS => [
                                        'statistics' => [[
                                            'attribs' => ['' => ['views' => '12345']],
                                        ]],
                                    ],
                                ],
                            ]],
                        ],
                    ],
                ]];
            }
        };

        $mockFeed = new class ($mockItem) {
            private $item;

            public function __construct($item)
            {
                $this->item = $item;
            }

            public function get_items($start = 0, $end = 0): array
            {
                return [$this->item];
            }
        };

        $result = $model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testParseChannelSkipsPlaysUpdateWithoutStatistics(): void
    {
        // Items without media statistics must not trigger a plays update
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(true);
        $mockAggro->expects($this->never())->method('setVideoPlays');

        $mockUtility = $this->createMock(UtilityModels::class);

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $mockItem = new class () {
            public function get_item_tags($namespace, $tag): array
            {
                return [['data' => 'existing_video_id']];
            }
        };

        $mockFeed = new class ($mockItem) {
            private $item;

            public function __construct($item)
            {
                $this->item = $item;
            }

            public function get_items($start = 0, $end = 0): array
            {
                return [$this->item];
            }
        };

        $result = $model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testSearchChannelReturnsFalseForEmptyFeed(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->searchChannel($mockFeed, 'any_video_id');
        $this->assertFalse($result);
    }

    public function testParseChannelHandlesEmptyFeedGracefully(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testSearchChannelParameterValidation(): void
    {
        // Test that method handles different parameter types appropriately
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        // Test with empty video ID
        $result = $this->model->searchChannel($mockFeed, '');
        $this->assertFalse($result);

        // Test with null video ID
        $result = $this->model->searchChannel($mockFeed, null);
        $this->assertFalse($result);
    }

    public function testModelMethodsReturnCorrectTypes(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        // Verify return types for all public methods
        $this->assertIsBool($this->model->searchChannel($mockFeed, 'test'));
        $this->assertIsInt($this->model->parseChannel($mockFeed));
    }

    public function testSearchChannelFlagsShort(): void
    {
        // Arrange
        $added = [];
        $model = $this->buildModelWithCannedShorts(null, $this->buildAggroCapturingAdds($added));

        $model->shorts = ['targetVideo' => true];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $result = $model->searchChannel($feed, 'targetVideo');

        // Assert
        $this->assertTrue($result);
        $this->assertSame(1, $added[0]['flag_short']);
        $this->assertSame(1, $model->shortCalls['targetVideo']);
        $this->assertSame(0, $model->sleepCalls);
    }

    public function testSearchChannelStoresRegularVideoWithoutShortFlag(): void
    {
        // Arrange
        $added = [];
        $model = $this->buildModelWithCannedShorts(null, $this->buildAggroCapturingAdds($added));

        $model->shorts = ['targetVideo' => false];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $model->searchChannel($feed, 'targetVideo');

        // Assert - Nothing but the feed, oEmbed, and the probe is read
        $this->assertSame(0, $added[0]['flag_short']);
        $this->assertArrayNotHasKey('video_duration', $added[0]);
        $this->assertSame(1, $model->shortCalls['targetVideo']);
    }

    public function testSearchChannelRetriesShortsCheckOnceWhenInconclusive(): void
    {
        // Arrange - One flaky probe should not let a Short through
        $added = [];
        $model = $this->buildModelWithCannedShorts(null, $this->buildAggroCapturingAdds($added));

        $model->shortSequence = ['targetVideo' => [null, true]];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $model->searchChannel($feed, 'targetVideo');

        // Assert - Second probe is trusted, after exactly one pause
        $this->assertSame(1, $added[0]['flag_short']);
        $this->assertSame(2, $model->shortCalls['targetVideo']);
        $this->assertSame(1, $model->sleepCalls);
    }

    public function testSearchChannelStoresRegularVideoWhenShortsCheckStaysInconclusive(): void
    {
        // Arrange - When the probe cannot answer, the video shows rather than vanishes
        $added = [];
        $model = $this->buildModelWithCannedShorts(null, $this->buildAggroCapturingAdds($added));

        $model->shortSequence = ['targetVideo' => [null, null]];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $model->searchChannel($feed, 'targetVideo');

        // Assert - Stored as a regular video and logged
        $this->assertSame(0, $added[0]['flag_short']);
        $this->assertSame(2, $model->shortCalls['targetVideo']);
        $this->assertLogged('warning', 'Shorts check for targetVideo was inconclusive. Stored as a regular video.');
    }

    public function testParseChannelFlagsShortsForEachNewVideo(): void
    {
        // Arrange - The channel sweep is a separate call site from searchChannel
        $added = [];
        $model = $this->buildModelWithCannedShorts(null, $this->buildAggroCapturingAdds($added));

        $model->shorts = ['videoOne' => true, 'videoTwo' => false];

        $feed = $this->makeFeed(
            '<title>Video One</title>' . $this->videoEntryXml('videoOne'),
            '<title>Video Two</title>' . $this->videoEntryXml('videoTwo'),
        );

        // Act
        $count = $model->parseChannel($feed);

        // Assert
        $this->assertSame(2, $count);
        $flags = array_column($added, 'flag_short', 'video_id');
        $this->assertSame(1, $flags['videoOne']);
        $this->assertSame(0, $flags['videoTwo']);
        $this->assertSame(1, $model->shortCalls['videoOne']);
        $this->assertSame(1, $model->shortCalls['videoTwo']);
    }
}
