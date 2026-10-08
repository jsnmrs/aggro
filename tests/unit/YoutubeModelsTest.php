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
    private int $originalBatchSize;
    private int $originalDelay;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageConfig                       = config('Storage');
        $this->originalBatchSize                   = $this->storageConfig->durationBatchSize;
        $this->originalDelay                       = $this->storageConfig->durationRequestDelay;
        $this->storageConfig->durationRequestDelay = 0;

        $this->model = new YoutubeModels();
    }

    protected function tearDown(): void
    {
        $this->storageConfig->durationBatchSize    = $this->originalBatchSize;
        $this->storageConfig->durationRequestDelay = $this->originalDelay;
        parent::tearDown();
    }

    /**
     * Build a YoutubeModels whose fetchDuration() returns canned results
     * keyed by video_id instead of scraping YouTube.
     *
     * Each fetch and each pause is counted so tests can assert on the
     * request budget without spending wall-clock time.
     *
     * @param UtilityModels|null $utilityModel Optional utility model override
     * @param AggroModels|null   $aggroModel   Optional aggro model override
     */
    private function buildModelWithCannedDurations(?UtilityModels $utilityModel = null, ?AggroModels $aggroModel = null): YoutubeModels
    {
        $utilityModel ??= $this->createMock(UtilityModels::class);

        return new class ($aggroModel, $utilityModel) extends YoutubeModels {
            /**
             * @var array<string, false|string>
             */
            public array $durations = [];

            /**
             * Per-video results consumed in order, ahead of $durations.
             *
             * @var array<string, list<false|string>>
             */
            public array $durationSequence = [];

            /**
             * @var array<string, bool>
             */
            public array $unavailable = [];

            /**
             * @var array<string, int>
             */
            public array $fetchCalls = [];

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

            protected function fetchDuration($videoId, &$unavailable = null)
            {
                $unavailable                = $this->unavailable[$videoId] ?? false;
                $this->fetchCalls[$videoId] = ($this->fetchCalls[$videoId] ?? 0) + 1;

                if (! empty($this->durationSequence[$videoId])) {
                    return array_shift($this->durationSequence[$videoId]);
                }

                return $this->durations[$videoId] ?? false;
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

    /**
     * Insert a YouTube video awaiting a duration.
     *
     * @param array $overrides Optional data to override defaults
     */
    private function insertVideoNeedingDuration(string $videoId, array $overrides = []): void
    {
        $defaults = [
            'video_id'             => $videoId,
            'aggro_date_added'     => date('Y-m-d H:i:s'),
            'aggro_date_updated'   => date('Y-m-d H:i:s'),
            'video_date_uploaded'  => date('Y-m-d H:i:s'),
            'video_title'          => 'Test Video',
            'video_type'           => 'youtube',
            'video_duration'       => 0,
            'flag_archive'         => 0,
            'flag_bad'             => 0,
            'duration_issue_count' => 0,
        ];

        $this->db->table('aggro_videos')->insert(array_merge($defaults, $overrides));
    }

    /**
     * Fetch a video row by video_id.
     */
    private function getVideoRow(string $videoId): array
    {
        return $this->db->table('aggro_videos')
            ->where('video_id', $videoId)
            ->get()
            ->getRowArray();
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

    public function testGetDurationMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getDuration'));
    }

    public function testGetDurationWithEmptyDatabase(): void
    {
        // With no videos having duration=0, getDuration should log "0 video durations fetched."
        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->expects($this->once())
            ->method('sendLog')
            ->with('0 video durations fetched.');

        $model  = new YoutubeModels(null, $mockUtility);
        $result = $model->getDuration();

        $this->assertTrue($result);
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

    public function testGetDurationReturnsBoolean(): void
    {
        $mockUtility = $this->createMock(UtilityModels::class);
        $model       = new YoutubeModels(null, $mockUtility);

        $result = $model->getDuration();

        $this->assertIsBool($result);
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
        // The dimension and duration lookups are blocked, so the video is
        // added with the 800x450 defaults and no duration.
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
        $this->assertSame(0, $added[0]['video_duration']);
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

    public function testGetDurationUpdatesVideoDatabase(): void
    {
        // Arrange
        $this->insertVideoNeedingDuration('good_video', ['duration_issue_count' => 4]);

        $model            = $this->buildModelWithCannedDurations();
        $model->durations = ['good_video' => '820'];

        // Act
        $model->getDuration();

        // Assert - Duration written and the failure count cleared
        $row = $this->getVideoRow('good_video');
        $this->assertSame(820, (int) $row['video_duration']);
        $this->assertSame(0, (int) $row['duration_issue_count']);
        $this->assertSame(0, (int) $row['flag_bad']);
    }

    public function testGetDurationFlagsBadWhenSourceReportsUnavailable(): void
    {
        // Arrange - YouTube answers 200 for deleted videos, so playabilityStatus
        // is the only signal that retrying will never succeed.
        $this->insertVideoNeedingDuration('gone_video');

        $model              = $this->buildModelWithCannedDurations();
        $model->unavailable = ['gone_video' => true];

        // Act
        $model->getDuration();

        // Assert - Flagged on the first failure, threshold path not taken
        $row = $this->getVideoRow('gone_video');
        $this->assertSame(1, (int) $row['flag_bad']);
        $this->assertSame(0, (int) $row['duration_issue_count']);
        $this->assertSame(0, (int) $row['video_duration']);
        $this->assertLogged('warning', 'Flagged video gone_video as bad — source reports it unavailable.');
    }

    public function testGetDurationRecordsRetiredVideoInSiteLog(): void
    {
        // Arrange - Retiring a video hides it from the site for good, so the
        // reason belongs in aggro_log where it can be read back.
        $messages = [];

        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->method('sendLog')->willReturnCallback(
            static function ($message) use (&$messages) {
                $messages[] = $message;

                return true;
            },
        );

        $this->insertVideoNeedingDuration('gone_video');

        $model              = $this->buildModelWithCannedDurations($mockUtility);
        $model->unavailable = ['gone_video' => true];

        // Act
        $model->getDuration();

        // Assert
        $this->assertContains('Retired gone_video. Source reports the video is unavailable.', $messages);
    }

    public function testGetDurationDoesNotRecordRetirementForAmbiguousFailure(): void
    {
        // Arrange - A transient failure may still recover, so nothing is retired
        $messages = [];

        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->method('sendLog')->willReturnCallback(
            static function ($message) use (&$messages) {
                $messages[] = $message;

                return true;
            },
        );

        $this->insertVideoNeedingDuration('flaky_video');

        $model = $this->buildModelWithCannedDurations($mockUtility);

        // Act
        $model->getDuration();

        // Assert
        $this->assertNotContains('Retired flaky_video. Source reports the video is unavailable.', $messages);
    }

    public function testGetDurationHandlesApiFailure(): void
    {
        // Arrange - A fetch failure with no availability signal is ambiguous
        $this->insertVideoNeedingDuration('flaky_video');

        $model = $this->buildModelWithCannedDurations();

        // Act
        $model->getDuration();

        // Assert - Counted, not flagged, so a transient blip can recover
        $row = $this->getVideoRow('flaky_video');
        $this->assertSame(1, (int) $row['duration_issue_count']);
        $this->assertSame(0, (int) $row['flag_bad']);
    }

    public function testGetDurationFlagsBadOnceIssueCountExceedsThreshold(): void
    {
        // Arrange
        $storageConfig = config('Storage');
        $this->insertVideoNeedingDuration('worn_out_video', [
            'duration_issue_count' => $storageConfig->durationIssueThreshold,
        ]);

        $model = $this->buildModelWithCannedDurations();

        // Act
        $model->getDuration();

        // Assert - One more failure crosses the threshold and retires the video
        $row = $this->getVideoRow('worn_out_video');
        $this->assertSame($storageConfig->durationIssueThreshold + 1, (int) $row['duration_issue_count']);
        $this->assertSame(1, (int) $row['flag_bad']);
    }

    public function testGetDurationSkipsVideosAlreadyFlaggedBad(): void
    {
        // Arrange - A retired video must never be picked up again
        $this->insertVideoNeedingDuration('retired_video', ['flag_bad' => 1]);

        $model = $this->buildModelWithCannedDurations();

        // Act
        $model->getDuration();

        // Assert
        $row = $this->getVideoRow('retired_video');
        $this->assertSame(0, (int) $row['duration_issue_count']);
    }

    public function testGetDurationLogsResults(): void
    {
        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->expects($this->once())
            ->method('sendLog')
            ->with($this->stringContains('video durations fetched'));

        $model = new YoutubeModels(null, $mockUtility);
        $model->getDuration();
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

    public function testGetDurationReturnsTrueWhenNoVideosNeedDuration(): void
    {
        // When there are no videos with duration=0, getDuration should still return true
        $mockUtility = $this->createMock(UtilityModels::class);
        $model       = new YoutubeModels(null, $mockUtility);

        $result = $model->getDuration();

        $this->assertTrue($result);
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

        // Skip getDuration test that requires aggro_videos table
        // $this->assertIsBool($this->model->getDuration());
    }

    public function testSearchChannelRetriesDurationOnceOnAmbiguousFailure(): void
    {
        // Arrange - One flaky watch-page fetch should not hide the video
        $added = [];
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

        $model->durationSequence = ['targetVideo' => [false, '820']];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $result = $model->searchChannel($feed, 'targetVideo');

        // Assert - Second attempt is stored, after exactly one pause
        $this->assertTrue($result);
        $this->assertSame('820', $added[0]['video_duration']);
        $this->assertSame(2, $model->fetchCalls['targetVideo']);
        $this->assertSame(1, $model->sleepCalls);
    }

    public function testSearchChannelDoesNotRetryWhenSourceReportsUnavailable(): void
    {
        // Arrange - A video the source says is gone will never yield a duration
        $added = [];
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

        $model->unavailable = ['targetVideo' => true];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $model->searchChannel($feed, 'targetVideo');

        // Assert
        $this->assertSame(0, $added[0]['video_duration']);
        $this->assertSame(1, $model->fetchCalls['targetVideo']);
        $this->assertSame(0, $model->sleepCalls);
    }

    public function testSearchChannelStoresZeroWhenRetryAlsoFails(): void
    {
        // Arrange - A sustained block fails both attempts
        $added = [];
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

        $model->durationSequence = ['targetVideo' => [false, false]];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $model->searchChannel($feed, 'targetVideo');

        // Assert - Stored as zero for the nightly job, no third attempt
        $this->assertSame(0, $added[0]['video_duration']);
        $this->assertSame(2, $model->fetchCalls['targetVideo']);
        $this->assertSame(1, $model->sleepCalls);
    }

    public function testParseChannelRetriesDurationForEachNewVideo(): void
    {
        // Arrange - The channel sweep is a separate call site from searchChannel
        $added = [];
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

        $model->durationSequence = [
            'videoOne' => [false, '820'],
            'videoTwo' => [false, '640'],
        ];

        $feed = $this->makeFeed(
            '<title>Video One</title>' . $this->videoEntryXml('videoOne'),
            '<title>Video Two</title>' . $this->videoEntryXml('videoTwo'),
        );

        // Act
        $result = $model->parseChannel($feed);

        // Assert - SimplePie does not promise item order, so key on video id
        $durations = array_column($added, 'video_duration', 'video_id');
        $this->assertSame(2, $result);
        $this->assertSame('820', $durations['videoOne']);
        $this->assertSame('640', $durations['videoTwo']);
        $this->assertSame(2, $model->fetchCalls['videoOne']);
        $this->assertSame(2, $model->fetchCalls['videoTwo']);
        $this->assertSame(2, $model->sleepCalls);
    }

    public function testGetDurationRespectsBatchSize(): void
    {
        // Arrange
        $this->storageConfig->durationBatchSize = 2;

        $this->insertVideoNeedingDuration('video_a');
        $this->insertVideoNeedingDuration('video_b');
        $this->insertVideoNeedingDuration('video_c');

        $model            = $this->buildModelWithCannedDurations();
        $model->durations = ['video_a' => '100', 'video_b' => '200', 'video_c' => '300'];

        // Act
        $model->getDuration();

        // Assert - Two fetched with one pause between them, one left over
        $updated = $this->db->table('aggro_videos')
            ->where('video_duration >', 0)
            ->countAllResults();
        $this->assertSame(2, $updated);
        $this->assertSame(1, $model->sleepCalls);
    }

    public function testGetDurationProcessesBacklogBeyondTen(): void
    {
        // Arrange - A backlog larger than the old fixed limit clears in one run
        $model            = $this->buildModelWithCannedDurations();
        $model->durations = [];

        for ($index = 1; $index <= 12; $index++) {
            $videoId = 'backlog_' . $index;
            $this->insertVideoNeedingDuration($videoId);
            $model->durations[$videoId] = '120';
        }

        // Act
        $model->getDuration();

        // Assert
        $remaining = $this->db->table('aggro_videos')
            ->where('video_duration', 0)
            ->countAllResults();
        $this->assertSame(0, $remaining);
        $this->assertSame(11, $model->sleepCalls);
    }

    public function testGetDurationFillsNewestFirst(): void
    {
        // Arrange - The front page shows newest first, so those should surface first
        $this->storageConfig->durationBatchSize = 1;

        $this->insertVideoNeedingDuration('older_video', ['aggro_date_added' => '2026-09-28 10:00:00']);
        $this->insertVideoNeedingDuration('newer_video', ['aggro_date_added' => '2026-10-05 10:00:00']);

        $model            = $this->buildModelWithCannedDurations();
        $model->durations = ['older_video' => '100', 'newer_video' => '200'];

        // Act
        $model->getDuration();

        // Assert
        $this->assertSame(200, (int) $this->getVideoRow('newer_video')['video_duration']);
        $this->assertSame(0, (int) $this->getVideoRow('older_video')['video_duration']);
    }

    public function testGetDurationLogsRemainingBacklog(): void
    {
        // Arrange - The log line should say how far behind the job is
        $this->storageConfig->durationBatchSize = 2;

        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->expects($this->once())
            ->method('sendLog')
            ->with('2 video durations fetched, 1 still waiting.');

        $this->insertVideoNeedingDuration('video_a');
        $this->insertVideoNeedingDuration('video_b');
        $this->insertVideoNeedingDuration('video_c');

        $model            = $this->buildModelWithCannedDurations($mockUtility);
        $model->durations = ['video_a' => '100', 'video_b' => '200', 'video_c' => '300'];

        // Act
        $model->getDuration();
    }

    public function testSearchChannelFlagsShortAndSkipsWatchPage(): void
    {
        // Arrange - A Short never needs a duration, so the watch page is not fetched
        $added = [];
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

        $model->shorts = ['targetVideo' => true];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $result = $model->searchChannel($feed, 'targetVideo');

        // Assert
        $this->assertTrue($result);
        $this->assertSame(1, $added[0]['flag_short']);
        $this->assertSame(0, $added[0]['video_duration']);
        $this->assertSame(1, $model->shortCalls['targetVideo']);
        $this->assertArrayNotHasKey('targetVideo', $model->fetchCalls);
        $this->assertSame(0, $model->sleepCalls);
    }

    public function testSearchChannelStoresRegularVideoWithoutShortFlag(): void
    {
        // Arrange
        $added = [];
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

        $model->shorts    = ['targetVideo' => false];
        $model->durations = ['targetVideo' => '820'];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $model->searchChannel($feed, 'targetVideo');

        // Assert
        $this->assertSame(0, $added[0]['flag_short']);
        $this->assertSame('820', $added[0]['video_duration']);
        $this->assertSame(1, $model->shortCalls['targetVideo']);
        $this->assertSame(1, $model->fetchCalls['targetVideo']);
    }

    public function testSearchChannelRetriesShortsCheckOnceWhenInconclusive(): void
    {
        // Arrange - One flaky probe should not let a Short through
        $added = [];
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

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
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

        $model->shortSequence = ['targetVideo' => [null, null]];
        $model->durations     = ['targetVideo' => '820'];

        $feed = $this->makeFeed('<title>Target Video</title>' . $this->videoEntryXml('targetVideo'));

        // Act
        $model->searchChannel($feed, 'targetVideo');

        // Assert - Stored as a regular video, with its duration, and logged
        $this->assertSame(0, $added[0]['flag_short']);
        $this->assertSame('820', $added[0]['video_duration']);
        $this->assertSame(2, $model->shortCalls['targetVideo']);
        $this->assertLogged('warning', 'Shorts check for targetVideo was inconclusive. Stored as a regular video.');
    }

    public function testParseChannelFlagsShortsForEachNewVideo(): void
    {
        // Arrange - The channel sweep is a separate call site from searchChannel
        $added = [];
        $model = $this->buildModelWithCannedDurations(null, $this->buildAggroCapturingAdds($added));

        $model->shorts    = ['videoOne' => true, 'videoTwo' => false];
        $model->durations = ['videoTwo' => '640'];

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
        $this->assertArrayNotHasKey('videoOne', $model->fetchCalls);
        $this->assertSame(1, $model->fetchCalls['videoTwo']);
    }

    public function testGetDurationFlagsShortsWithoutFetchingDuration(): void
    {
        // Arrange - A Short in the backlog is flagged and skipped, not fetched
        $messages = [];

        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->method('sendLog')->willReturnCallback(
            static function ($message) use (&$messages) {
                $messages[] = $message;

                return true;
            },
        );

        $this->insertVideoNeedingDuration('short_video');

        $model         = $this->buildModelWithCannedDurations($mockUtility);
        $model->shorts = ['short_video' => true];

        // Act
        $model->getDuration();

        // Assert
        $row = $this->getVideoRow('short_video');
        $this->assertSame(1, (int) $row['flag_short']);
        $this->assertSame(0, (int) $row['video_duration']);
        $this->assertSame(0, (int) $row['duration_issue_count']);
        $this->assertArrayNotHasKey('short_video', $model->fetchCalls);
        $this->assertContains('Flagged short_video as a Short.', $messages);
    }

    public function testGetDurationSkipsFlaggedShorts(): void
    {
        // Arrange - A flagged Short keeps a zero duration and must never be re-selected
        $this->insertVideoNeedingDuration('known_short', ['flag_short' => 1]);

        $model = $this->buildModelWithCannedDurations();

        // Act
        $model->getDuration();

        // Assert
        $this->assertSame([], $model->shortCalls);
        $this->assertSame([], $model->fetchCalls);
        $this->assertSame(0, (int) $this->getVideoRow('known_short')['duration_issue_count']);
    }

    public function testGetDurationFetchesDurationWhenShortsCheckIsInconclusive(): void
    {
        // Arrange - An inconclusive probe falls through to the watch page
        $this->insertVideoNeedingDuration('maybe_short');

        $model            = $this->buildModelWithCannedDurations();
        $model->shorts    = ['maybe_short' => null];
        $model->durations = ['maybe_short' => '120'];

        // Act
        $model->getDuration();

        // Assert
        $row = $this->getVideoRow('maybe_short');
        $this->assertSame(120, (int) $row['video_duration']);
        $this->assertSame(0, (int) $row['flag_short']);
    }

    public function testGetDurationLogMessageCountsShorts(): void
    {
        // Arrange
        $mockUtility = $this->createMock(UtilityModels::class);
        $messages    = [];
        $mockUtility->method('sendLog')->willReturnCallback(
            static function ($message) use (&$messages) {
                $messages[] = $message;

                return true;
            },
        );

        $this->insertVideoNeedingDuration('video_a');
        $this->insertVideoNeedingDuration('short_b');

        $model            = $this->buildModelWithCannedDurations($mockUtility);
        $model->durations = ['video_a' => '100'];
        $model->shorts    = ['short_b' => true];

        // Act
        $model->getDuration();

        // Assert
        $this->assertContains('1 video durations fetched, 1 flagged as Shorts.', $messages);
    }
}
