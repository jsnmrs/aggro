<?php

use App\Models\UtilityModels;
use App\Services\PlaysService;
use Config\Storage;
use Tests\Support\ServiceTestCase;

/**
 * @internal
 */
final class PlaysServiceTest extends ServiceTestCase
{
    private PlaysService $service;
    private Storage $storageConfig;
    private int $originalBatchSize;
    private int $originalDelay;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageConfig                    = config('Storage');
        $this->originalBatchSize                = $this->storageConfig->playsBatchSize;
        $this->originalDelay                    = $this->storageConfig->playsRequestDelay;
        $this->storageConfig->playsRequestDelay = 0;

        $this->service = $this->buildServiceWithCannedResponses();
    }

    protected function tearDown(): void
    {
        $this->storageConfig->playsBatchSize    = $this->originalBatchSize;
        $this->storageConfig->playsRequestDelay = $this->originalDelay;
        parent::tearDown();
    }

    /**
     * Build a PlaysService whose fetchPlays() returns canned responses
     * keyed by video_id instead of hitting YouTube/Vimeo.
     *
     * @param UtilityModels|null $utilityModel Optional utility model override
     */
    private function buildServiceWithCannedResponses(?UtilityModels $utilityModel = null): PlaysService
    {
        return new class (null, $utilityModel) extends PlaysService {
            public array $responses = [];
            public array $statuses  = [];

            protected function fetchPlays(object $video, ?int &$httpStatus = null)
            {
                $httpStatus = $this->statuses[$video->video_id] ?? 200;

                if (! array_key_exists($video->video_id, $this->responses)) {
                    return false;
                }

                return $this->responses[$video->video_id];
            }
        };
    }

    /**
     * Build a test video row.
     *
     * @param array $overrides Optional data to override defaults
     */
    private function makeVideo(array $overrides = []): array
    {
        $defaults = [
            'video_id'              => 'test_' . uniqid(),
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'aggro_date_updated'    => date('Y-m-d H:i:s'),
            'video_date_uploaded'   => date('Y-m-d H:i:s'),
            'flag_archive'          => 0,
            'flag_bad'              => 0,
            'video_plays'           => 100,
            'video_title'           => 'Test Video',
            'video_thumbnail_url'   => 'https://example.com/thumb.jpg',
            'video_width'           => 1920,
            'video_height'          => 1080,
            'video_aspect_ratio'    => '16:9',
            'video_duration'        => 300,
            'video_source_id'       => 'test_source',
            'video_source_username' => 'testuser',
            'video_source_url'      => 'https://example.com/video',
            'video_type'            => 'youtube',
        ];

        return array_merge($defaults, $overrides);
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

    public function testRefreshPlaysUpdatesPlayCounts()
    {
        // Arrange
        $this->insertTestVideo($this->makeVideo(['video_id' => 'yt_video', 'video_type' => 'youtube']));
        $this->insertTestVideo($this->makeVideo(['video_id' => 'vimeo_video', 'video_type' => 'vimeo']));

        $this->service->responses = [
            'yt_video'    => 5000,
            'vimeo_video' => 250,
        ];

        // Act
        $result = $this->service->refreshPlays();

        // Assert
        $this->assertTrue($result);

        $ytRow = $this->getVideoRow('yt_video');
        $this->assertSame(5000, (int) $ytRow['video_plays']);
        $this->assertNotNull($ytRow['plays_date_updated']);
        $this->assertSame(0, (int) $ytRow['plays_issue_count']);

        $vimeoRow = $this->getVideoRow('vimeo_video');
        $this->assertSame(250, (int) $vimeoRow['video_plays']);
        $this->assertNotNull($vimeoRow['plays_date_updated']);
    }

    public function testRefreshPlaysStampsWithoutOverwritingWhenStatsHidden()
    {
        // Arrange - Vimeo owners can hide play stats; null means checked but unavailable
        $this->insertTestVideo($this->makeVideo([
            'video_id'    => 'hidden_stats',
            'video_type'  => 'vimeo',
            'video_plays' => 750,
        ]));

        $this->service->responses = ['hidden_stats' => null];

        // Act
        $this->service->refreshPlays();

        // Assert
        $row = $this->getVideoRow('hidden_stats');
        $this->assertSame(750, (int) $row['video_plays']);
        $this->assertNotNull($row['plays_date_updated']);
        $this->assertSame(0, (int) $row['plays_issue_count']);
    }

    public function testRefreshPlaysRecordsIssueOnFetchFailure()
    {
        // Arrange
        $this->insertTestVideo($this->makeVideo([
            'video_id'    => 'gone_video',
            'video_plays' => 900,
        ]));

        $this->service->responses = ['gone_video' => false];

        // Act
        $this->service->refreshPlays();

        // Assert - Plays untouched, issue recorded, cursor still advances
        $row = $this->getVideoRow('gone_video');
        $this->assertSame(900, (int) $row['video_plays']);
        $this->assertSame(1, (int) $row['plays_issue_count']);
        $this->assertNotNull($row['plays_date_updated']);
        $this->assertSame(0, (int) $row['flag_bad']);
    }

    public function testRefreshPlaysFlagsBadImmediatelyOn404()
    {
        // Arrange
        $this->insertTestVideo($this->makeVideo([
            'video_id'    => 'deleted_video',
            'video_plays' => 900,
        ]));

        $this->service->responses = ['deleted_video' => false];
        $this->service->statuses  = ['deleted_video' => 404];

        // Act
        $this->service->refreshPlays();

        // Assert - Flagged bad on first 404, threshold path not taken
        $row = $this->getVideoRow('deleted_video');
        $this->assertSame(1, (int) $row['flag_bad']);
        $this->assertSame(0, (int) $row['plays_issue_count']);
        $this->assertSame(900, (int) $row['video_plays']);
        $this->assertLogged('warning', 'Flagged video deleted_video as bad — source returned 404.');
    }

    public function testRefreshPlaysRecordsIssueOnTransientFailure()
    {
        // Arrange
        $this->insertTestVideo($this->makeVideo([
            'video_id'    => 'flaky_video',
            'video_plays' => 900,
        ]));

        $this->service->responses = ['flaky_video' => false];
        $this->service->statuses  = ['flaky_video' => 500];

        // Act
        $this->service->refreshPlays();

        // Assert - Transient failure keeps the threshold path
        $row = $this->getVideoRow('flaky_video');
        $this->assertSame(0, (int) $row['flag_bad']);
        $this->assertSame(1, (int) $row['plays_issue_count']);
    }

    public function testRefreshPlaysDoesNotOverwriteWithZero()
    {
        // Arrange
        $this->insertTestVideo($this->makeVideo([
            'video_id'    => 'zero_response',
            'video_plays' => 1200,
        ]));

        $this->service->responses = ['zero_response' => 0];

        // Act
        $this->service->refreshPlays();

        // Assert - A real count is never clobbered by zero
        $row = $this->getVideoRow('zero_response');
        $this->assertSame(1200, (int) $row['video_plays']);
        $this->assertNotNull($row['plays_date_updated']);
    }

    public function testRefreshPlaysSkipsBadVideos()
    {
        // Arrange
        $this->insertTestVideo($this->makeVideo([
            'video_id' => 'bad_video',
            'flag_bad' => 1,
        ]));

        $this->service->responses = ['bad_video' => 999];

        // Act
        $this->service->refreshPlays();

        // Assert
        $row = $this->getVideoRow('bad_video');
        $this->assertSame(100, (int) $row['video_plays']);
        $this->assertNull($row['plays_date_updated']);
    }

    public function testRefreshPlaysIncludesArchivedVideos()
    {
        // Arrange - Archived videos are the whole point of the refresher
        $this->insertTestVideo($this->makeVideo([
            'video_id'     => 'archived_video',
            'flag_archive' => 1,
        ]));

        $this->service->responses = ['archived_video' => 800];

        // Act
        $this->service->refreshPlays();

        // Assert
        $row = $this->getVideoRow('archived_video');
        $this->assertSame(800, (int) $row['video_plays']);
        $this->assertNotNull($row['plays_date_updated']);
    }

    public function testRefreshPlaysRespectsBatchSize()
    {
        // Arrange
        $this->storageConfig->playsBatchSize = 2;

        $this->insertTestVideo($this->makeVideo(['video_id' => 'batch_one']));
        $this->insertTestVideo($this->makeVideo(['video_id' => 'batch_two']));
        $this->insertTestVideo($this->makeVideo(['video_id' => 'batch_three']));

        $this->service->responses = [
            'batch_one'   => 10,
            'batch_two'   => 20,
            'batch_three' => 30,
        ];

        // Act
        $this->service->refreshPlays();

        // Assert - Only batch-size videos processed
        $processed = $this->db->table('aggro_videos')
            ->where('plays_date_updated IS NOT NULL')
            ->countAllResults();
        $this->assertSame(2, $processed);
    }

    public function testRefreshPlaysFlagsBadAfterRepeatedFailures()
    {
        // Arrange - Already at the threshold; one more failure tips it over
        $this->insertTestVideo($this->makeVideo([
            'video_id'          => 'chronic_failure',
            'plays_issue_count' => 10,
        ]));

        $this->service->responses = ['chronic_failure' => false];

        // Act
        $this->service->refreshPlays();

        // Assert
        $row = $this->getVideoRow('chronic_failure');
        $this->assertSame(11, (int) $row['plays_issue_count']);
        $this->assertSame(1, (int) $row['flag_bad']);
    }

    public function testRefreshPlaysHandlesEmptyDatabase()
    {
        // Act
        $result = $this->service->refreshPlays();

        // Assert
        $this->assertTrue($result);
    }

    public function testRefreshPlaysLogsUpdateCount()
    {
        // Arrange
        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->expects($this->once())
            ->method('sendLog')
            ->with($this->stringContains('play counts updated'));

        $service            = $this->buildServiceWithCannedResponses($mockUtility);
        $service->responses = ['logged_video' => 42];

        $this->insertTestVideo($this->makeVideo(['video_id' => 'logged_video']));

        // Act
        $service->refreshPlays();
    }
}
