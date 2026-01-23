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

        $query = $this->db->table('aggro_videos')
            ->select('video_id, video_thumbnail_url')
            ->where('flag_archive', 0)
            ->where('flag_bad', 0)
            ->get();

        $thumbs = $query->getResult();

        $storageConfig = config('Storage');

        foreach ($thumbs as $thumb) {
            $path = $storageConfig->getThumbnailPath($thumb->video_id);

            if (file_exists($path)) {
                continue;
            }

            $message = $thumb->video_id . ' missing thumbnail';
            if (fetch_thumbnail($thumb->video_id, $thumb->video_thumbnail_url)) {
                $message .= ' — fetched.';
            }
            $this->utilityModel->sendLog($message);
        }

        return true;
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
}
