<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All YouTube interactions with aggro_* tables.
 */
class YoutubeModels extends Model
{
    protected $aggroModel;
    protected $utilityModel;

    public function __construct(?AggroModels $aggroModel = null, ?UtilityModels $utilityModel = null)
    {
        parent::__construct();
        $this->aggroModel = $aggroModel ?? new AggroModels();
        $this->utilityModel = $utilityModel ?? new UtilityModels();
    }

    /**
     * Search YouTube feed for a specific video.
     *
     * @param object $feed
     *                        Fetched YouTube feed.
     * @param string $videoId
     *                        Video ID to look for.
     *
     * @return bool
     *              Video added.
     */
    public function searchChannel($feed, $videoId)
    {
        helper('youtube');

        foreach ($feed->get_items(0, 0) as $item) {
            $currentVideo   = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'videoId');
            $currentVideoId = $currentVideo[0]['data'];

            if ($currentVideoId === $videoId && ! $this->aggroModel->checkVideo($currentVideoId)) {
                $video = youtube_parse_meta($item);
                $this->aggroModel->addVideo($video);

                return true;
            }
        }

        return false;
    }

    /**
     * Parse YouTube feed for videos.
     *
     * @param object $feed
     *                     Fetched YouTube feed.
     *
     * @return int
     *             Number of videos added.
     */
    public function parseChannel($feed)
    {
        helper('youtube');
        $addCount = 0;

        foreach ($feed->get_items(0, 0) as $item) {
            $currentVideo   = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'videoId');
            $currentVideoId = $currentVideo[0]['data'];

            if (! $this->aggroModel->checkVideo($currentVideoId)) {
                $video = youtube_parse_meta($item);
                $this->aggroModel->addVideo($video);
                $addCount++;
            }
        }

        if ($addCount >= 1) {
            $message = 'Ran YouTube fetch. Added ' . $addCount . ' new-to-me videos.';
            $this->utilityModel->sendLog($message);
        }

        return $addCount;
    }

    /**
     * Get duration for YouTube videos.
     *
     * Write count of updated videos to log.
     *
     * @return bool
     *              Archive complete.
     *
     * @see sendLog()
     */
    public function getDuration()
    {
        helper('youtube');

        $query = $this->db->table('aggro_videos')
            ->where('flag_archive', 0)
            ->where('flag_bad', 0)
            ->where('video_duration', 0)
            ->where('video_type', 'youtube')
            ->limit(10)
            ->get();

        if ($query === false) {
            return false;
        }

        $update = count($query->getResultArray());

        if ($update > 0) {
            $results = $query->getResult();

            foreach ($results as $result) {
                $videoDuration = youtube_get_duration($result->video_id);
                if ($videoDuration !== false && is_numeric($videoDuration)) {
                    $sql = 'UPDATE aggro_videos SET video_duration = ? WHERE video_id = ?';
                    $this->db->query($sql, [$videoDuration, $result->video_id]);
                }
                if ($videoDuration === false) {
                    log_message('error', 'Failed to get duration for video ' . $result->video_id);
                }
            }
        }

        $message = $update . ' video durations fetched.';
        $this->utilityModel->sendLog($message);

        return true;
    }
}
