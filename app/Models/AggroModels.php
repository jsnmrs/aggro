<?php

namespace App\Models;

use CodeIgniter\Model;

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

        $sql = "INSERT INTO aggro_videos (video_id, aggro_date_added, aggro_date_updated, video_date_uploaded, flag_archive, flag_bad, video_plays, video_title, video_thumbnail_url, video_width, video_height, video_aspect_ratio, video_duration, video_source_id, video_source_username, video_source_url, video_type) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
            $video['video_type']
        ]);

        if ($video['flag_archive'] === 0 && fetch_thumbnail($video['video_id'], $video['video_thumbnail_url'])) {
            $message = 'Added ' . $video['video_type'] . ' ' . $video['video_id'] . ' and fetched thumbnail.';
        }

        if ($video['flag_archive'] === 1) {
            $message = 'Added and archived ' . $video['video_type'] . ' ' . $video['video_id'] . '.';
        }

        $utilityModel->sendLog($message);

        return true;
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

        $sql    = "SELECT * FROM aggro_videos WHERE video_date_uploaded <= DATE_SUB(?,INTERVAL 31 DAY) AND flag_archive=0 AND flag_bad=0";
        $query  = $this->db->query($sql, [$now]);
        $update = count($query->getResultArray());

        if ($update > 0) {
            $sql   = "UPDATE aggro_videos SET flag_archive = 1 WHERE video_date_uploaded <= DATE_SUB(?,INTERVAL 31 DAY) AND flag_archive=0 AND flag_bad=0";
            $query = $this->db->query($sql, [$now]);
        }

        $message = $update . ' videos archived.';
        $utilityModel->sendLog($message);

        return true;
    }

    /**
     * Check thumbnails.
     */
    public function checkThumbs()
    {
        $utilityModel = new UtilityModels();
        helper('aggro');

        $sql    = 'SELECT video_id, video_thumbnail_url FROM aggro_videos WHERE flag_archive=0 AND flag_bad=0';
        $query  = $this->db->query($sql);
        $thumbs = $query->getResult();

        foreach ($thumbs as $thumb) {
            $path = ROOTPATH . 'public/thumbs/' . $thumb->video_id . '.webp';

            if (! file_exists($path)) {
                $message = $thumb->video_id . ' missing thumbnail';

                if (fetch_thumbnail($thumb->video_id, $thumb->video_thumbnail_url)) {
                    $message .= ' &mdash; fetched.';
                    $utilityModel->sendLog($message);
                }
            }
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
        $utilityModel = new UtilityModels();
        $sql          = "SELECT video_id FROM aggro_videos WHERE video_id=?";
        $query        = $this->db->query($sql, [$videoid]);
        $update       = count($query->getResultArray());

        if ($update > 0) {
            return true;
        }

        $message = $videoid . ' is new to me.';
        $utilityModel->sendLog($message);

        return false;
    }

    /**
     * Clean thumbnail directory.
     */
    public function cleanThumbs()
    {
        $utilityModel = new UtilityModels();
        $thumbs       = ROOTPATH . 'public/thumbs/*.webp';

        $files = glob($thumbs);
        $now   = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * 45) {
                    unlink($file);
                }
            }
        }

        $message = 'cleaned thumbnails.';
        $utilityModel->sendLog($message);

        return true;
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
     * @return array
     *               All fields for all video channels matching arguments.
     */
    public function getChannels($stale = 30, $type = 'youtube', $limit = 10)
    {
        $utilityModel = new UtilityModels();
        $now          = date('Y-m-d H:i:s');
        $sql          = "SELECT * FROM aggro_sources WHERE source_type=? AND source_date_updated <= DATE_SUB(?,INTERVAL ? MINUTE) ORDER BY source_date_updated ASC LIMIT ?";
        $query        = $this->db->query($sql, [$type, $now, $stale, $limit]);
        $update       = count($query->getResultArray());

        if ($update > 0) {
            $message = 'Looking for ' . $limit . ' ' . $type . ' channels. Found ' . $update . ' stale ' . $type . ' channels. Updating...';
            $utilityModel->sendLog($message);

            return $query->getResult();
        }

        if ($update === 0) {
            $message = 'Looking for ' . $limit . ' ' . $type . ' channels. Found 0 stale ' . $type . ' channels.';
            $utilityModel->sendLog($message);

            return false;
        }
    }

    /**
     * Get single video.
     *
     * @param string $slug
     *                     Video id.
     *
     * @return array
     *               Video data from table or FALSE.
     */
    public function getVideo($slug)
    {
        $slug  = esc($slug);
        $sql   = "SELECT * FROM aggro_videos WHERE video_id='{$slug}' LIMIT 1";
        $query = $this->db->query($sql);
        if ($query->getRowArray() === null) {
            return false;
        }

        return $query->getRowArray();
    }

    /**
     * Get all videos.
     *
     * @param string $sort
     *                        - Recent.
     * @param string $range
     *                        - Year.
     *                        - Month.
     *                        - Week.
     * @param string $perpage
     *                        Results per page.
     * @param string $offset
     *                        Result starting offset.
     *
     * @return string
     *                Video data from table.
     */
    public function getVideos($sort = 'recent', $range = 'month', $perpage = 10, $offset = 0)
    {
        $now = date('Y-m-d H:i:s');

        if ($sort === 'recent') {
            $sortField = 'aggro_date_added';
        }

        if ($range === 'year') {
            $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 365 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 SECOND)';
        }

        if ($range === 'month') {
            $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 31 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 SECOND)';
        }

        if ($range === 'week') {
            $constrict = 'AND aggro_date_added BETWEEN DATE_SUB("' . $now . '", INTERVAL 7 DAY) AND DATE_SUB("' . $now . '", INTERVAL 30 SECOND)';
        }

        $sql   = 'SELECT * FROM aggro_videos WHERE flag_bad = 0 AND flag_archive = 0 AND video_duration >= 61 AND aggro_date_updated <> "0000-00-00 00:00:00"' . $constrict . 'ORDER BY ' . $sortField . ' DESC LIMIT ' . $perpage . ' OFFSET ' . $offset;
        $query = $this->db->query($sql);

        return $query->getResult();
    }

    /**
     * Get all videos total.
     *
     * @return string
     *                Total number of active videos.
     */
    public function getVideosTotal()
    {
        $sql   = "SELECT aggro_id FROM aggro_videos WHERE flag_bad = 0 AND flag_archive = 0 AND video_duration >= 61 AND aggro_date_updated <> '0000-00-00 00:00:00'";
        $query = $this->db->query($sql);

        return count($query->getResultArray());
    }

    /**
     * Update video source last fetch timestamp.
     *
     * @param string $sourceSlug
     *                           Source slug.
     */
    public function updateChannel($sourceSlug)
    {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE aggro_sources
            SET source_date_updated = ?
            WHERE source_slug = ?";
        $this->db->query($sql, [$now, $sourceSlug]);
    }
}
