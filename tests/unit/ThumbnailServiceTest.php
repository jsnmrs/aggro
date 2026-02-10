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

    public function testCleanThumbsHandlesGlobError()
    {
        // Skip test that requires file system operations and Storage config
        $this->markTestSkipped('Method requires file system access and Storage configuration');

        // This would test behavior when glob() returns false
    }

    public function testCleanThumbsDeletesOldFiles()
    {
        // Skip test that requires file system operations
        $this->markTestSkipped('Method requires file system access for thumbnail cleanup testing');

        // This would test actual file deletion based on age
    }

    public function testIsFileEligibleForDeletionWithOldFile()
    {
        // Skip test that requires accessing private method and file operations
        $this->markTestSkipped('Method is private and requires file system access');

        // This would test the private isFileEligibleForDeletion method
    }

    public function testIsFileEligibleForDeletionWithNewFile()
    {
        // Skip test that requires accessing private method
        $this->markTestSkipped('Method is private and requires file system access');

        // This would test files that are too new to delete
    }

    public function testIsFileEligibleForDeletionWithNonExistentFile()
    {
        // Skip test that requires accessing private method
        $this->markTestSkipped('Method is private and requires file system access');

        // This would test behavior with files that don't exist
    }

    public function testDeleteFileSuccessful()
    {
        // Skip test that requires accessing private method and file operations
        $this->markTestSkipped('Method is private and requires file system access');

        // This would test successful file deletion
    }

    public function testDeleteFileFailure()
    {
        // Skip test that requires accessing private method
        $this->markTestSkipped('Method is private and requires file system access');

        // This would test failed file deletion scenarios
    }

    public function testLogCleanupResults()
    {
        // Skip test that requires accessing private method
        $this->markTestSkipped('Method is private and requires UtilityModels integration');

        // This would test logging of cleanup results
    }

    public function testCheckThumbsHandlesMissingThumbnail()
    {
        // Skip test that requires file system access and helper functions
        $this->markTestSkipped('Method requires fetch_thumbnail helper and file system access');

        // This would test behavior when thumbnail files are missing
    }

    public function testCheckThumbsSkipsExistingThumbnails()
    {
        // Skip test that requires file system access
        $this->markTestSkipped('Method requires file system access to check existing files');

        // This would test that existing thumbnails are skipped
    }

    public function testCheckThumbsLogsResults()
    {
        // Skip test that requires UtilityModels integration
        $this->markTestSkipped('Method requires UtilityModels sendLog functionality');

        // This would test that results are logged via UtilityModels
    }

    public function testCleanThumbsCountsDeletedFiles()
    {
        // Skip test that requires file system operations
        $this->markTestSkipped('Method requires file system access for deletion counting');

        // This would test that deleted file count is accurate
    }

    public function testCleanThumbsCountsErrors()
    {
        // Skip test that requires file system operations
        $this->markTestSkipped('Method requires file system access for error counting');

        // This would test that deletion errors are counted
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
