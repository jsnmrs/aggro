<?php

namespace App\Services;

use App\Models\UtilityModels;
use Config\Database;

/**
 * Service for handling thumbnail operations.
 */
class ThumbnailService
{
    protected $db;
    protected $utilityModel;

    public function __construct()
    {
        $this->db           = Database::connect();
        $this->utilityModel = new UtilityModels();
    }

    /**
     * Check and fetch missing thumbnails.
     *
     * @return bool
     *              Thumbnail check complete.
     */
    public function checkThumbs()
    {
        helper('aggro');

        $storageConfig = config('Storage');
        $batchSize     = 100;
        $offset        = 0;

        do {
            $query = $this->db->table('aggro_videos')
                ->select('video_id, video_thumbnail_url')
                ->where('flag_archive', 0)
                ->where('flag_bad', 0)
                ->limit($batchSize, $offset)
                ->get();

            $thumbs = $query->getResult();

            foreach ($thumbs as $thumb) {
                $path = $storageConfig->getThumbnailPath($thumb->video_id);

                if (file_exists($path)) {
                    continue;
                }

                $httpStatus = null;
                $message    = $thumb->video_id . ' missing thumbnail';

                if (fetch_thumbnail($thumb->video_id, $thumb->video_thumbnail_url, $httpStatus)) {
                    $message .= ' — fetched.';
                    $this->resetThumbnailIssueCount($thumb->video_id);
                } elseif ($httpStatus === 404) {
                    $this->incrementThumbnailIssueCount($thumb->video_id);
                }

                $this->utilityModel->sendLog($message);
            }

            $offset += $batchSize;
        } while (count($thumbs) === $batchSize);

        return true;
    }

    /**
     * Flag videos with chronic thumbnail failures as bad.
     *
     * Videos with thumbnail_issue_count above the threshold
     * are permanently flagged bad so they stop being fetched,
     * displayed, and retried.
     *
     * @return int Number of videos flagged
     */
    public function flagBrokenThumbnails()
    {
        $threshold = 10;

        $videos = $this->db->table('aggro_videos')
            ->select('video_id')
            ->where('thumbnail_issue_count >', $threshold)
            ->where('flag_bad', 0)
            ->get()
            ->getResult();

        $count = 0;

        foreach ($videos as $video) {
            $this->db->table('aggro_videos')
                ->where('video_id', $video->video_id)
                ->update(['flag_bad' => 1]);

            log_message('error', 'Flagged video ' . $video->video_id . ' as bad — thumbnail 404 count exceeded threshold (' . $threshold . ').');
            $count++;
        }

        return $count;
    }

    /**
     * Clean thumbnail directory.
     *
     * @return bool
     *              Cleanup complete.
     */
    public function cleanThumbs()
    {
        $storageConfig = config('Storage');
        $files         = glob($storageConfig->getThumbnailGlob());

        if ($files === false) {
            log_message('error', 'Failed to glob thumbnail files');

            return false;
        }

        $deletedCount = 0;
        $errorCount   = 0;
        $maxAge       = $storageConfig->getCleanupAgeSeconds();

        foreach ($files as $file) {
            if (! $this->isFileEligibleForDeletion($file, $maxAge)) {
                continue;
            }

            if ($this->deleteFile($file)) {
                $deletedCount++;

                continue;
            }

            $errorCount++;
        }

        $this->logCleanupResults($deletedCount, $errorCount);

        return true;
    }

    /**
     * Check if a file is eligible for deletion based on age.
     *
     * @param string $file   The file path to check
     * @param int    $maxAge Maximum age in seconds
     *
     * @return bool True if file should be deleted, false otherwise
     */
    private function isFileEligibleForDeletion($file, $maxAge)
    {
        if (! is_file($file)) {
            return false;
        }

        $fileAge = filemtime($file);
        if ($fileAge === false) {
            log_message('warning', 'Failed to get modification time for thumbnail: ' . $file);

            return false;
        }

        return (time() - $fileAge) >= $maxAge;
    }

    /**
     * Delete a file and log any errors.
     *
     * @param string $file The file path to delete
     *
     * @return bool True if deletion succeeded, false otherwise
     */
    private function deleteFile($file)
    {
        if (unlink($file)) {
            return true;
        }

        log_message('error', 'Failed to delete thumbnail: ' . $file);

        return false;
    }

    /**
     * Log the cleanup results.
     *
     * @param int $deletedCount Number of files deleted
     * @param int $errorCount   Number of deletion errors
     */
    private function logCleanupResults($deletedCount, $errorCount)
    {
        $message = 'Cleaned thumbnails: ' . $deletedCount . ' deleted';

        if ($errorCount > 0) {
            $message .= ', ' . $errorCount . ' errors';
        }

        $this->utilityModel->sendLog($message);
    }

    /**
     * Increment the thumbnail issue count for a video.
     *
     * @param string $videoId The video ID
     */
    private function incrementThumbnailIssueCount($videoId)
    {
        $this->db->table('aggro_videos')
            ->where('video_id', $videoId)
            ->set('thumbnail_issue_count', 'thumbnail_issue_count + 1', false)
            ->update();
    }

    /**
     * Reset the thumbnail issue count for a video.
     *
     * @param string $videoId The video ID
     */
    private function resetThumbnailIssueCount($videoId)
    {
        $this->db->table('aggro_videos')
            ->where('video_id', $videoId)
            ->update(['thumbnail_issue_count' => 0]);
    }
}
