<?php

namespace App\Repositories;

use App\Models\UtilityModels;
use Config\Database;
use Exception;

/**
 * Repository for video-related database operations.
 */
class VideoRepository
{
    protected $db;
    protected $utilityModel;

    public function __construct()
    {
        $this->db           = Database::connect();
        $this->utilityModel = new UtilityModels();
    }

    /**
     * Add new video to video table.
     *
     * @param array<string, mixed> $video
     *
     * @return bool
     *              Video added to video table.
     */
    public function addVideo(array $video)
    {
        helper('aggro');

        try {
            if (! $this->insertVideoRecord($video)) {
                return false;
            }

            $message = $this->generateVideoMessage($video);
            $this->utilityModel->sendLog($message);

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
        $message = $videoid . ' is new to me.';
        $this->utilityModel->sendLog($message);
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
        $baseWhere     = $this->getActiveVideoConditions($storageConfig->minVideoDuration);
        $sql           = 'SELECT * FROM aggro_videos ' . $baseWhere . $constrict . 'ORDER BY ' . $sortField . ' DESC LIMIT ? OFFSET ?';
        $query         = $this->db->query($sql, [(int) $perpage, (int) $offset]);

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
     * Get the WHERE conditions for active videos.
     *
     * @param int $minVideoDuration
     *
     * @return string
     */
    private function getActiveVideoConditions($minVideoDuration)
    {
        return 'WHERE flag_bad = 0 AND flag_archive = 0 AND video_duration >= ' . (int) $minVideoDuration . ' AND aggro_date_updated <> "0000-00-00 00:00:00"';
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
        $baseWhere     = $this->getActiveVideoConditions($storageConfig->minVideoDuration);
        $sql           = 'SELECT COUNT(*) as total FROM aggro_videos ' . $baseWhere;
        $query         = $this->db->query($sql);

        return (int) $query->getRow()->total;
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
}
