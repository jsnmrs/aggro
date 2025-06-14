<?php

namespace App\Services;

use App\Models\UtilityModels;
use Config\Database;
use Exception;

/**
 * Service for handling video archiving operations.
 */
class ArchiveService
{
    protected $db;
    protected $utilityModel;

    public function __construct()
    {
        $this->db           = Database::connect();
        $this->utilityModel = new UtilityModels();
    }

    /**
     * Archive old videos.
     *
     * @return bool
     *              Archive operation complete.
     */
    public function archiveVideos()
    {
        $now = date('Y-m-d H:i:s');

        try {
            $updateCount = $this->performArchiveOperation($now);
            $message     = $updateCount . ' videos archived.';
            $this->utilityModel->sendLog($message);

            return true;
        } catch (Exception $e) {
            log_message('error', 'Exception in archiveVideos: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Perform the archive operation in a transaction.
     *
     * @param string $now
     *
     * @return int Number of videos archived
     *
     * @throws Exception
     */
    private function performArchiveOperation($now)
    {
        $this->db->transStart();

        // Optimized: Single UPDATE operation that returns affected rows count
        $updateCount = $this->updateArchiveFlags($now);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', 'Transaction failed in archiveVideos');

            throw new Exception('Archive transaction failed');
        }

        return $updateCount;
    }

    /**
     * Update archive flags for eligible videos and return count.
     *
     * @param string $now
     *
     * @return int Number of videos archived
     *
     * @throws Exception
     */
    private function updateArchiveFlags($now)
    {
        $storageConfig = config('Storage');
        $cutoffDate    = date('Y-m-d H:i:s', strtotime("-{$storageConfig->archiveDays} days"));

        $result = $this->db->table('aggro_videos')
            ->where('video_date_uploaded <=', $cutoffDate)
            ->where('flag_archive', 0)
            ->where('flag_bad', 0)
            ->update(['flag_archive' => 1]);

        if ($result === false) {
            throw new Exception('Failed to update archive flag');
        }

        return $this->db->affectedRows();
    }
}
