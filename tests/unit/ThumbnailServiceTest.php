<?php

use App\Services\ThumbnailService;
use Tests\Support\LocalHttpServerTrait;
use Tests\Support\ServiceTestCase;
use Tests\Support\TemporaryStorageTrait;

/**
 * @internal
 */
final class ThumbnailServiceTest extends ServiceTestCase
{
    use LocalHttpServerTrait;
    use TemporaryStorageTrait;

    private ThumbnailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ThumbnailService();
    }

    public function testCheckThumbsFetchesMissingThumbnails()
    {
        $this->insertTestVideo($this->makeTestVideo('missing_thumb', [
            'video_thumbnail_url' => $this->localUrl('/body/image'),
        ]));

        $this->withThumbnailDirectory(function (): void {
            $this->assertTrue($this->service->checkThumbs());

            $path = config('Storage')->getThumbnailPath('missing_thumb');
            $this->assertFileExists($path);
            $this->assertSame(IMAGETYPE_WEBP, getimagesize($path)[2]);
        });
    }

    public function testCheckThumbsLeavesExistingThumbnailsAlone()
    {
        $this->insertTestVideo($this->makeTestVideo('has_thumb', [
            'video_thumbnail_url' => $this->localUrl('/body/image'),
        ]));

        $this->withThumbnailDirectory(function (): void {
            $path = config('Storage')->getThumbnailPath('has_thumb');
            file_put_contents($path, 'existing');

            $this->assertTrue($this->service->checkThumbs());

            $this->assertSame('existing', file_get_contents($path));
        });
    }

    public function testCheckThumbsOnlyProcessesActiveVideos()
    {
        $image = $this->localUrl('/body/image');
        $this->insertTestVideo($this->makeTestVideo('active_video', ['video_thumbnail_url' => $image]));
        $this->insertTestVideo($this->makeTestVideo('archived_video', ['video_thumbnail_url' => $image, 'flag_archive' => 1]));
        $this->insertTestVideo($this->makeTestVideo('bad_video', ['video_thumbnail_url' => $image, 'flag_bad' => 1]));

        $this->withThumbnailDirectory(function (): void {
            $storageConfig = config('Storage');

            $this->assertTrue($this->service->checkThumbs());

            $this->assertFileExists($storageConfig->getThumbnailPath('active_video'));
            $this->assertFileDoesNotExist($storageConfig->getThumbnailPath('archived_video'));
            $this->assertFileDoesNotExist($storageConfig->getThumbnailPath('bad_video'));
        });
    }

    public function testCleanThumbsRemovesOnlyOldThumbnails()
    {
        $this->withThumbnailDirectory(function (string $directory): void {
            $old   = $directory . 'old.webp';
            $fresh = $directory . 'fresh.webp';
            file_put_contents($old, 'old');
            file_put_contents($fresh, 'fresh');
            touch($old, time() - config('Storage')->getCleanupAgeSeconds() - 60);

            $this->assertTrue($this->service->cleanThumbs());

            $this->assertFileDoesNotExist($old);
            $this->assertFileExists($fresh);
        });
    }

    public function testCheckThumbsHandlesEmptyDatabase()
    {
        $this->withThumbnailDirectory(function (string $directory): void {
            $this->assertTrue($this->service->checkThumbs());

            $this->assertSame([], glob($directory . '*'));
        });
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
            'video_source_id'       => 'test_source',
            'video_source_username' => 'testuser',
            'video_source_url'      => 'https://example.com/video',
            'video_type'            => 'youtube',
        ], $overrides);
    }
}
