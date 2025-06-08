<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

/**
 * All interactions with aggro_* tables.
 */
class AggroModels extends Model
{
    /**
     * Add video metadata to aggro_videos.
     *
     * @return bool
     *              Video added.
     *
     * @see sendLog()
     */
    public function addVideo(array $video)
    {
        $utilityModel = new UtilityModels();
        helper('aggro');

        try {
            if (! $this->insertVideoRecord($video)) {
                return false;
            }

            $message = $this->generateVideoMessage($video);
            $utilityModel->sendLog($message);

            return true;
        } catch (Exception $e) {
            log_message('error', 'Exception in addVideo: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Insert video record into database.
     *
     * @return bool
     */
    private function insertVideoRecord(array $video)
    {
        $this->db->transStart();

        $sql = 'INSERT INTO aggro_videos (video_id, aggro_date_added, aggro_date_updated, video_date_uploaded, flag_archive, flag_bad, video_plays, video_title, video_thumbnail_url, video_width, video_height, video_aspect_ratio, video_duration, video_source_id, video_source_username, video_source_url, video_type) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $this->db->query($sql, [
            $video['video_id'],
            $video['aggro_date_added'],
            $video['aggro_date_updated'],
            $video['video_date_uploaded'],
            $video['flag_archive'],
            $video['video_plays'],
            $video['video_title'],
            $video['video_thumbnail_url'],
            $video['video_width'],
            $video['video_height'],
            $video['video_aspect_ratio'],
            $video['video_duration'],
            $video['video_source_id'],
            $video['video_source_username'],
            $video['video_source_url'],
            $video['video_type'],
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', 'Failed to add video: ' . json_encode($video));

            return false;
        }

        return true;
    }

    /**
     * Generate message for video addition.
     *
     * @return string
     */
    private function generateVideoMessage(array $video)
    {
        $baseMessage = 'Added ' . $video['video_type'] . ' ' . $video['video_id'];

        if ($video['flag_archive'] === 1) {
            return $baseMessage . ' and archived.';
        }

        try {
            $thumbnailFetched = fetch_thumbnail($video['video_id'], $video['video_thumbnail_url']);

            return $baseMessage . ($thumbnailFetched ? ' and fetched thumbnail.' : ' but failed to fetch thumbnail.');
        } catch (Exception $e) {
            log_message('error', 'Failed to fetch thumbnail for ' . $video['video_id'] . ': ' . $e->getMessage());

            return $baseMessage . ' but failed to fetch thumbnail.';
        }
    }

    /**
     * Archive videos older than 31 days by setting archive flag in video table.
     *
     * Write count of archived videos to log.
     *
     * @return bool
     *              Archive complete.
     *
     * @see sendLog()
     */
    public function archiveVideos()
    {
        $utilityModel = new UtilityModels();
        $now          = date('Y-m-d H:i:s');

        try {
            $updateCount = $this->performArchiveOperation($now);
            $message     = $updateCount . ' videos archived.';
            $utilityModel->sendLog($message);

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
        $sql           = 'UPDATE aggro_videos SET flag_archive = 1 WHERE video_date_uploaded <= DATE_SUB(?,INTERVAL ? DAY) AND flag_archive=0 AND flag_bad=0';
        $result        = $this->db->query($sql, [$now, $storageConfig->archiveDays]);

        if ($result === false) {
            throw new Exception('Failed to update archive flag');
        }

        return $this->db->affectedRows();
    }

    /**
     * Check thumbnails.
     *
     * @return bool
     *              Thumbnail check complete.
     */
    public function checkThumbs()
    {
        $utilityModel = new UtilityModels();
        helper('aggro');

        $sql    = 'SELECT video_id, video_thumbnail_url FROM aggro_videos WHERE flag_archive=0 AND flag_bad=0';
        $query  = $this->db->query($sql);
        $thumbs = $query->getResult();

        $storageConfig = config('Storage');

        foreach ($thumbs as $thumb) {
            $path = $storageConfig->getThumbnailPath($thumb->video_id);

            if (file_exists($path)) {
                continue;
            }

            $message = $thumb->video_id . ' missing thumbnail';
            if (fetch_thumbnail($thumb->video_id, $thumb->video_thumbnail_url)) {
                $message .= ' &mdash; fetched.';
            }
            $utilityModel->sendLog($message);
        }

        return true;
    }

    /**
     * Check if video exists in video table.
     *
     * @param string $videoid
     *                        Videoid to check.
     *
     * @return bool
     *              Video exists in video table.
     */
    public function checkVideo($videoid)
    {
        try {
            $exists = $this->videoExists($videoid);
            if (! $exists) {
                $this->logNewVideo($videoid);
            }

            return $exists;
        } catch (Exception $e) {
            log_message('error', 'Exception in checkVideo: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Check if video exists in database.
     *
     * @param string $videoid
     *
     * @return bool
     *
     * @throws Exception
     */
    private function videoExists($videoid)
    {
        $sql   = 'SELECT video_id FROM aggro_videos WHERE video_id=?';
        $query = $this->db->query($sql, [$videoid]);

        if ($query === false) {
            log_message('error', 'Failed to check video existence for: ' . $videoid);

            throw new Exception('Database query failed');
        }

        return count($query->getResultArray()) > 0;
    }

    /**
     * Log that a video is new.
     *
     * @param string $videoid
     */
    private function logNewVideo($videoid)
    {
        $utilityModel = new UtilityModels();
        $message      = $videoid . ' is new to me.';
        $utilityModel->sendLog($message);
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
        $utilityModel = new UtilityModels();
        $message      = 'Cleaned thumbnails: ' . $deletedCount . ' deleted';

        if ($errorCount > 0) {
            $message .= ', ' . $errorCount . ' errors';
        }

        $utilityModel->sendLog($message);
    }

    /**
     * Get list of video channels that haven't been updated within timeframe.
     *
     * @param string $stale
     *                      Time in minutes to consider a channel stale.
     * @param string $type
     *                      Type of channel to grab:
     *                      - site.
     *                      - youtube.
     *                      - vimeo.
     * @param string $limit
     *                      Maximum number of channels to grab.
     *
     * @return array|false
     *                     All fields for all video channels matching arguments.
     */
    public function getChannels($stale = '30', $type = 'youtube', $limit = '10')
    {
        $channels = $this->fetchStaleChannels($stale, $type, $limit);
        $this->logChannelSearchResult($channels, $type, $limit);

        return $channels;
    }

    /**
     * Fetch stale channels from database.
     *
     * @param string $stale
     * @param string $type
     * @param string $limit
     *
     * @return array|false
     */
    private function fetchStaleChannels($stale, $type, $limit)
    {
        $now     = date('Y-m-d H:i:s');
        $sql     = 'SELECT * FROM aggro_sources WHERE source_type=? AND source_date_updated <= DATE_SUB(?,INTERVAL ? MINUTE) ORDER BY source_date_updated ASC LIMIT ?';
        $query   = $this->db->query($sql, [$type, $now, (int) $stale, (int) $limit]);
        $results = $query->getResultArray();

        return count($results) > 0 ? $query->getResult() : false;
    }

    /**
     * Log channel search results.
     *
     * @param array|false $channels
     * @param string      $type
     * @param string      $limit
     */
    private function logChannelSearchResult($channels, $type, $limit)
    {
        $utilityModel = new UtilityModels();
        $count        = $channels === false ? 0 : count($channels);
        $updateStatus = $count > 0 ? ' Updating...' : '';
        $message      = "Looking for {$limit} {$type} channels. Found {$count} stale {$type} channels.{$updateStatus}";

        $utilityModel->sendLog($message);
    }

    /**
     * Get single video.
     *
     * @param string $slug
     *                     Video id.
     *
     * @return array|false
     *                     Video data from table or FALSE.
     */
    public function getVideo($slug)
    {
        $sql   = 'SELECT * FROM aggro_videos WHERE video_id=? LIMIT 1';
        $query = $this->db->query($sql, [$slug]);
        if ($query->getRowArray() === null) {
            return false;
        }

        return $query->getRowArray();
    }

    /**
     * Get all videos.
     *
     * @param string $range
     *                        - Year.
     *                        - Month.
     *                        - Week.
     * @param string $perpage
     *                        Results per page.
     * @param string $offset
     *                        Result starting offset.
     *
     * @return array
     *               Video data from table.
     */
    public function getVideos($range = 'month', $perpage = '10', $offset = '0')
    {
        $now           = date('Y-m-d H:i:s');
        $sortField     = 'aggro_date_added';
        $constrict     = $this->getRangeConstraint($range, $now);
        $storageConfig = config('Storage');
        $baseWhere     = 'WHERE flag_bad = 0 AND flag_archive = 0 AND video_duration >= ? AND aggro_date_updated <> "0000-00-00 00:00:00"';
        $sql           = 'SELECT * FROM aggro_videos ' . $baseWhere . $constrict . 'ORDER BY ' . $sortField . ' DESC LIMIT ? OFFSET ?';
        $query         = $this->db->query($sql, [$storageConfig->minVideoDuration, (int) $perpage, (int) $offset]);

        return $query->getResult();
    }

    /**
     * Get range constraint for SQL query.
     *
     * @param string $range
     * @param string $now
     *
     * @return string
     */
    private function getRangeConstraint($range, $now)
    {
        $intervals = ['year' => 365, 'week' => 7, 'month' => 31];
        $days      = $intervals[$range] ?? 31;

        return 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL ' . $days . ' DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 SECOND)';
    }

    /**
     * Get all videos total.
     *
     * @return int
     *             Total number of active videos.
     */
    public function getVideosTotal()
    {
        $storageConfig = config('Storage');
        $sql           = "SELECT aggro_id FROM aggro_videos WHERE flag_bad = 0 AND flag_archive = 0 AND video_duration >= ? AND aggro_date_updated <> '0000-00-00 00:00:00'";
        $query         = $this->db->query($sql, [$storageConfig->minVideoDuration]);

        return count($query->getResultArray());
    }

    /**
     * Update video source last fetch timestamp.
     *
     * @param string $sourceSlug
     *                           Source slug.
     *
     * @return void
     */
    public function updateChannel($sourceSlug)
    {
        $now = date('Y-m-d H:i:s');
        $sql = 'UPDATE aggro_sources
            SET source_date_updated = ?
            WHERE source_slug = ?';
        $this->db->query($sql, [$now, $sourceSlug]);
    }
}
