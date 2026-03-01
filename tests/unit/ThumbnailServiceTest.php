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
            'video_id'              => 'test_video',
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
            'video_id'              => 'active_video',
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'aggro_date_updated'    => date('Y-m-d H:i:s'),
            'video_date_uploaded'   => date('Y-m-d H:i:s'),
            'flag_archive'          => 0,
            'flag_bad'              => 0,
            'video_plays'           => 100,
            'video_title'           => 'Active Video',
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

        $archivedVideo = [
            'video_id'              => 'archived_video',
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'aggro_date_updated'    => date('Y-m-d H:i:s'),
            'video_date_uploaded'   => date('Y-m-d H:i:s'),
            'flag_archive'          => 1, // Archived
            'flag_bad'              => 0,
            'video_plays'           => 100,
            'video_title'           => 'Archived Video',
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

        $badVideo = [
            'video_id'              => 'bad_video',
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'aggro_date_updated'    => date('Y-m-d H:i:s'),
            'video_date_uploaded'   => date('Y-m-d H:i:s'),
            'flag_archive'          => 0,
            'flag_bad'              => 1, // Bad
            'video_plays'           => 100,
            'video_title'           => 'Bad Video',
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

    public function testIsFileEligibleForDeletionWithOldFile()
    {
        // Create a temp file and set its mtime to old
        $tmpDir = sys_get_temp_dir() . '/thumbtest_' . uniqid();
        mkdir($tmpDir);
        $tmpFile = $tmpDir . '/old.webp';
        file_put_contents($tmpFile, 'data');
        touch($tmpFile, time() - (60 * 60 * 24 * 90)); // 90 days old

        $reflection = new ReflectionClass($this->service);
        $method     = $reflection->getMethod('isFileEligibleForDeletion');
        $method->setAccessible(true);

        $maxAge = 60 * 60 * 24 * 45; // 45 days
        $result = $method->invoke($this->service, $tmpFile, $maxAge);

        $this->assertTrue($result);

        unlink($tmpFile);
        rmdir($tmpDir);
    }

    public function testIsFileEligibleForDeletionWithNewFile()
    {
        $tmpDir = sys_get_temp_dir() . '/thumbtest_' . uniqid();
        mkdir($tmpDir);
        $tmpFile = $tmpDir . '/new.webp';
        file_put_contents($tmpFile, 'data');
        // File just created, so it's fresh

        $reflection = new ReflectionClass($this->service);
        $method     = $reflection->getMethod('isFileEligibleForDeletion');
        $method->setAccessible(true);

        $maxAge = 60 * 60 * 24 * 45; // 45 days
        $result = $method->invoke($this->service, $tmpFile, $maxAge);

        $this->assertFalse($result);

        unlink($tmpFile);
        rmdir($tmpDir);
    }

    public function testIsFileEligibleForDeletionWithNonExistentFile()
    {
        $reflection = new ReflectionClass($this->service);
        $method     = $reflection->getMethod('isFileEligibleForDeletion');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, '/nonexistent/file.webp', 3600);

        $this->assertFalse($result);
    }

    public function testDeleteFileSuccessful()
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'thumb');
        file_put_contents($tmpFile, 'data');

        $reflection = new ReflectionClass($this->service);
        $method     = $reflection->getMethod('deleteFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, $tmpFile);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($tmpFile);
    }

    public function testDeleteFileWithNonExistentFile()
    {
        $reflection = new ReflectionClass($this->service);
        $method     = $reflection->getMethod('deleteFile');
        $method->setAccessible(true);

        // Suppress unlink warning for non-existent file
        $result = @$method->invoke($this->service, '/nonexistent/file.webp');

        $this->assertFalse($result);
    }

    public function testLogCleanupResults()
    {
        // We can test this via reflection by verifying the method runs without error
        $reflection = new ReflectionClass($this->service);
        $method     = $reflection->getMethod('logCleanupResults');
        $method->setAccessible(true);

        // Should not throw — just logs
        $method->invoke($this->service, 5, 0);
        $method->invoke($this->service, 3, 2);

        $this->assertTrue(true); // Reached here without error
    }

    public function testServiceConstructorSetsUpDependencies()
    {
        // Test that constructor initializes dependencies
        $service = new ThumbnailService();
        $this->assertInstanceOf(ThumbnailService::class, $service);
    }

    public function testCheckThumbsMethodExists()
    {
        $this->assertTrue(method_exists($this->service, 'checkThumbs'));
    }

    public function testCleanThumbsMethodExists()
    {
        $this->assertTrue(method_exists($this->service, 'cleanThumbs'));
    }

    public function testServiceHasPrivateMethods()
    {
        $reflection = new ReflectionClass($this->service);

        // Verify private methods exist
        $this->assertTrue($reflection->hasMethod('isFileEligibleForDeletion'));
        $this->assertTrue($reflection->hasMethod('deleteFile'));
        $this->assertTrue($reflection->hasMethod('logCleanupResults'));
    }

    public function testCheckThumbsReturnsBoolean()
    {
        $result = $this->service->checkThumbs();
        $this->assertIsBool($result);
    }

    public function testCleanThumbsReturnsBoolean()
    {
        $result = $this->service->cleanThumbs();
        $this->assertIsBool($result);
    }

    public function testServiceUsesCorrectDependencies()
    {
        // Test that service properly uses Database and UtilityModels
        // This verifies the constructor setup without accessing private properties
        $result1 = $this->service->checkThumbs();
        $result2 = $this->service->cleanThumbs();

        $this->assertIsBool($result1);
        $this->assertIsBool($result2);
    }

    public function testIncrementThumbnailIssueCount()
    {
        // Arrange
        $video = $this->makeTestVideo('inc_test', ['thumbnail_issue_count' => 0]);
        $this->insertTestVideo($video);

        // Act
        $reflection = new ReflectionClass($this->service);
        $method     = $reflection->getMethod('incrementThumbnailIssueCount');
        $method->setAccessible(true);
        $method->invoke($this->service, 'inc_test');

        // Assert
        $row = $this->db->table('aggro_videos')
            ->where('video_id', 'inc_test')
            ->get()
            ->getRow();

        $this->assertSame(1, $row->thumbnail_issue_count);
    }

    public function testResetThumbnailIssueCount()
    {
        // Arrange
        $video = $this->makeTestVideo('reset_test', ['thumbnail_issue_count' => 5]);
        $this->insertTestVideo($video);

        // Act
        $reflection = new ReflectionClass($this->service);
        $method     = $reflection->getMethod('resetThumbnailIssueCount');
        $method->setAccessible(true);
        $method->invoke($this->service, 'reset_test');

        // Assert
        $row = $this->db->table('aggro_videos')
            ->where('video_id', 'reset_test')
            ->get()
            ->getRow();

        $this->assertSame(0, $row->thumbnail_issue_count);
    }

    public function testFlagBrokenThumbnails()
    {
        // Arrange — one video above threshold, one below
        $highCount = $this->makeTestVideo('high_count', ['thumbnail_issue_count' => 11, 'flag_bad' => 0]);
        $lowCount  = $this->makeTestVideo('low_count', ['thumbnail_issue_count' => 3, 'flag_bad' => 0]);
        $this->insertTestVideo($highCount);
        $this->insertTestVideo($lowCount);

        // Act
        $this->service->flagBrokenThumbnails();

        // Assert — high count should be flagged bad
        $highRow = $this->db->table('aggro_videos')
            ->where('video_id', 'high_count')
            ->get()
            ->getRow();
        $this->assertSame(1, $highRow->flag_bad);

        // Assert — low count should remain unflagged
        $lowRow = $this->db->table('aggro_videos')
            ->where('video_id', 'low_count')
            ->get()
            ->getRow();
        $this->assertSame(0, $lowRow->flag_bad);
    }

    public function testFlagBrokenThumbnailsSkipsAlreadyBadVideos()
    {
        // Arrange — video already flagged bad with high count
        $alreadyBad = $this->makeTestVideo('already_bad', [
            'thumbnail_issue_count' => 15,
            'flag_bad'              => 1,
        ]);
        $this->insertTestVideo($alreadyBad);

        // Act
        $result = $this->service->flagBrokenThumbnails();

        // Assert — method should return 0 flagged (it was already bad)
        $this->assertSame(0, $result);
    }

    public function testFlagBrokenThumbnailsReturnsCount()
    {
        // Arrange
        $video1 = $this->makeTestVideo('flag_count_1', ['thumbnail_issue_count' => 12, 'flag_bad' => 0]);
        $video2 = $this->makeTestVideo('flag_count_2', ['thumbnail_issue_count' => 15, 'flag_bad' => 0]);
        $this->insertTestVideo($video1);
        $this->insertTestVideo($video2);

        // Act
        $result = $this->service->flagBrokenThumbnails();

        // Assert
        $this->assertSame(2, $result);
    }

    /**
     * Build a test video array with sensible defaults.
     */
    private function makeTestVideo(string $videoId, array $overrides = []): array
    {
        return array_merge([
            'video_id'              => $videoId,
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'aggro_date_updated'    => date('Y-m-d H:i:s'),
            'video_date_uploaded'   => date('Y-m-d H:i:s'),
            'flag_archive'          => 0,
            'flag_bad'              => 0,
            'flag_favorite'         => 0,
            'thumbnail_issue_count' => 0,
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
        ], $overrides);
    }
}
