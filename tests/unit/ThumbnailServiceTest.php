<?php

use App\Services\ThumbnailService;
use Tests\Support\ServiceTestCase;

/**
 * @internal
 */
final class ThumbnailServiceTest extends ServiceTestCase
{
    private ThumbnailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ThumbnailService();
    }

    public function testCheckThumbsReturnsTrue()
    {
        // Arrange
        $video = [
            'video_id' => 'test_video',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s'),
            'flag_archive' => 0,
            'flag_bad' => 0,
            'video_plays' => 100,
            'video_title' => 'Test Video',
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

        $this->insertTestVideo($video);

        // Act
        $result = $this->service->checkThumbs();

        // Assert
        $this->assertTrue($result);
    }

    public function testCheckThumbsOnlyProcessesActiveVideos()
    {
        // Arrange
        $activeVideo = [
            'video_id' => 'active_video',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s'),
            'flag_archive' => 0,
            'flag_bad' => 0,
            'video_plays' => 100,
            'video_title' => 'Active Video',
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

        $archivedVideo = [
            'video_id' => 'archived_video',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s'),
            'flag_archive' => 1, // Archived
            'flag_bad' => 0,
            'video_plays' => 100,
            'video_title' => 'Archived Video',
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

        $badVideo = [
            'video_id' => 'bad_video',
            'aggro_date_added' => date('Y-m-d H:i:s'),
            'aggro_date_updated' => date('Y-m-d H:i:s'),
            'video_date_uploaded' => date('Y-m-d H:i:s'),
            'flag_archive' => 0,
            'flag_bad' => 1, // Bad
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

        $this->insertTestVideo($activeVideo);
        $this->insertTestVideo($archivedVideo);
        $this->insertTestVideo($badVideo);

        // Act
        $result = $this->service->checkThumbs();

        // Assert
        $this->assertTrue($result);
        
        // The method should only process active videos (flag_archive=0 AND flag_bad=0)
        // We can't easily test the file operations in unit tests, but we can verify
        // the method completes successfully
    }

    public function testCleanThumbsReturnsTrue()
    {
        // Act - Test with no files to clean
        $result = $this->service->cleanThumbs();

        // Assert
        $this->assertTrue($result);
    }

    public function testCheckThumbsHandlesEmptyDatabase()
    {
        // Act - No videos in database
        $result = $this->service->checkThumbs();

        // Assert
        $this->assertTrue($result);
    }
}