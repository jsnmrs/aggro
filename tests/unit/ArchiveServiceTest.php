<?php

use App\Services\ArchiveService;
use Tests\Support\ServiceTestCase;

/**
 * @internal
 */
final class ArchiveServiceTest extends ServiceTestCase
{
    private ArchiveService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ArchiveService();
    }

    public function testArchiveVideosArchivesOldVideos()
    {
        // Arrange - Create videos with different upload dates
        $oldVideo = [
            'video_id' => 'old_video',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s', strtotime('-100 days')), // Old video
            'flag_archive' => 0,
            'flag_bad' => 0,
            'video_plays' => 100,
            'video_title' => 'Old Video',
            'video_thumbnail_url' => 'https://example.com/thumb.jpg',
            'video_width' => 1920,
            'video_height' => 1080,
            'video_aspect_ratio' => '16:9',
            'video_duration' => 300,
            'video_source_id' => 'test_source',
            'video_source_username' => 'testuser',
            'video_source_url' => 'https://example.com/video',
            'video_type' => 'youtube',
        ];

        $recentVideo = [
            'video_id' => 'recent_video',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s', strtotime('-1 day')), // Recent video
            'flag_archive' => 0,
            'flag_bad' => 0,
            'video_plays' => 100,
            'video_title' => 'Recent Video',
            'video_thumbnail_url' => 'https://example.com/thumb.jpg',
            'video_width' => 1920,
            'video_height' => 1080,
            'video_aspect_ratio' => '16:9',
            'video_duration' => 300,
            'video_source_id' => 'test_source',
            'video_source_username' => 'testuser',
            'video_source_url' => 'https://example.com/video',
            'video_type' => 'youtube',
        ];

        $this->insertTestVideo($oldVideo);
        $this->insertTestVideo($recentVideo);

        // Act
        $result = $this->service->archiveVideos();

        // Assert
        $this->assertTrue($result);

        // Check that old video was archived
        $archivedVideo = $this->db->table('aggro_videos')
            ->where('video_id', 'old_video')
            ->get()
            ->getRowArray();
        $this->assertEquals(1, $archivedVideo['flag_archive']);

        // Check that recent video was not archived
        $recentVideoResult = $this->db->table('aggro_videos')
            ->where('video_id', 'recent_video')
            ->get()
            ->getRowArray();
        $this->assertEquals(0, $recentVideoResult['flag_archive']);
    }

    public function testArchiveVideosDoesNotArchiveAlreadyArchivedVideos()
    {
        // Arrange
        $alreadyArchivedVideo = [
            'video_id' => 'already_archived',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s', strtotime('-100 days')),
            'flag_archive' => 1, // Already archived
            'flag_bad' => 0,
            'video_plays' => 100,
            'video_title' => 'Already Archived Video',
            'video_thumbnail_url' => 'https://example.com/thumb.jpg',
            'video_width' => 1920,
            'video_height' => 1080,
            'video_aspect_ratio' => '16:9',
            'video_duration' => 300,
            'video_source_id' => 'test_source',
            'video_source_username' => 'testuser',
            'video_source_url' => 'https://example.com/video',
            'video_type' => 'youtube',
        ];

        $this->insertTestVideo($alreadyArchivedVideo);

        // Act
        $result = $this->service->archiveVideos();

        // Assert
        $this->assertTrue($result);

        // Verify the archived video remains archived (no double processing)
        $archivedCount = $this->countRecords('aggro_videos', ['flag_archive' => 1]);
        $this->assertEquals(1, $archivedCount);
    }

    public function testArchiveVideosDoesNotArchiveBadVideos()
    {
        // Arrange
        $badVideo = [
            'video_id' => 'bad_video',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s', strtotime('-100 days')),
            'flag_archive' => 0,
            'flag_bad' => 1, // Bad video
            'video_plays' => 100,
            'video_title' => 'Bad Video',
            'video_thumbnail_url' => 'https://example.com/thumb.jpg',
            'video_width' => 1920,
            'video_height' => 1080,
            'video_aspect_ratio' => '16:9',
            'video_duration' => 300,
            'video_source_id' => 'test_source',
            'video_source_username' => 'testuser',
            'video_source_url' => 'https://example.com/video',
            'video_type' => 'youtube',
        ];

        $this->insertTestVideo($badVideo);

        // Act
        $result = $this->service->archiveVideos();

        // Assert
        $this->assertTrue($result);

        // Verify bad video was not archived
        $badVideoResult = $this->db->table('aggro_videos')
            ->where('video_id', 'bad_video')
            ->get()
            ->getRowArray();
        $this->assertEquals(0, $badVideoResult['flag_archive']);
    }

    public function testArchiveVideosReturnsTrue()
    {
        // Arrange - No videos to archive
        $recentVideo = [
            'video_id' => 'recent_video',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'flag_archive' => 0,
            'flag_bad' => 0,
            'video_plays' => 100,
            'video_title' => 'Recent Video',
            'video_thumbnail_url' => 'https://example.com/thumb.jpg',
            'video_width' => 1920,
            'video_height' => 1080,
            'video_aspect_ratio' => '16:9',
            'video_duration' => 300,
            'video_source_id' => 'test_source',
            'video_source_username' => 'testuser',
            'video_source_url' => 'https://example.com/video',
            'video_type' => 'youtube',
        ];

        $this->insertTestVideo($recentVideo);

        // Act
        $result = $this->service->archiveVideos();

        // Assert
        $this->assertTrue($result);
    }

    public function testArchiveVideosHandlesEmptyDatabase()
    {
        // Act - No videos in database
        $result = $this->service->archiveVideos();

        // Assert
        $this->assertTrue($result);
    }
}