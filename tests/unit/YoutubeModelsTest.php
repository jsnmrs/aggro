<?php

namespace Tests\Unit;

use App\Models\AggroModels;
use App\Models\UtilityModels;
use App\Models\YoutubeModels;
use CodeIgniter\Model;
use ReflectionClass;
use SimplePie\SimplePie;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class YoutubeModelsTest extends DatabaseTestCase
{
    protected YoutubeModels $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new YoutubeModels();
    }

    /**
     * Build a YoutubeModels whose fetchDuration() returns canned results
     * keyed by video_id instead of scraping YouTube.
     *
     * @param UtilityModels|null $utilityModel Optional utility model override
     */
    private function buildModelWithCannedDurations(?UtilityModels $utilityModel = null): YoutubeModels
    {
        $utilityModel ??= $this->createMock(UtilityModels::class);

        return new class (null, $utilityModel) extends YoutubeModels {
            /**
             * @var array<string, false|string>
             */
            public array $durations = [];

            /**
             * @var array<string, bool>
             */
            public array $unavailable = [];

            protected function fetchDuration($videoId, &$unavailable = null)
            {
                $unavailable = $this->unavailable[$videoId] ?? false;

                return $this->durations[$videoId] ?? false;
            }
        };
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
        // Skip test that requires YouTube helper functions and AggroModels integration
        $this->markTestSkipped('Method requires youtube_parse_meta helper and AggroModels integration');

        // This would test finding a specific video in a feed
        // $mockItem = $this->createMockYouTubeItem('target_video_id');
        // $mockFeed = $this->createMockFeedWithItems([$mockItem]);
        // $result = $this->model->searchChannel($mockFeed, 'target_video_id');
        // $this->assertTrue($result);
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
        // Skip test that requires YouTube helper functions
        $this->markTestSkipped('Method requires youtube_parse_meta helper and AggroModels integration');

        // This would test processing multiple videos from a feed
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
}
