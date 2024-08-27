<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All YouTube interactions with aggro_* tables.
 */
class YoutubeModels extends Model
{
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
        $aggroModel = new AggroModels();
        helper('youtube');

        foreach ($feed->get_items(0, 0) as $item) {
            $currentVideo   = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'videoId');
            $currentVideoId = $currentVideo[0]['data'];

            if ($currentVideoId === $videoId && ! $aggroModel->checkVideo($currentVideoId)) {
                $video = youtube_parse_meta($item);
                $aggroModel->addVideo($video);

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
        $aggroModel   = new AggroModels();
        $utilityModel = new UtilityModels();
        helper('youtube');
        $addCount = 0;

        foreach ($feed->get_items(0, 0) as $item) {
            $currentVideo   = $item->get_item_tags('http://www.youtube.com/xml/schemas/2015', 'videoId');
            $currentVideoId = $currentVideo[0]['data'];

            if (! $aggroModel->checkVideo($currentVideoId)) {
                $video = youtube_parse_meta($item);
                $aggroModel->addVideo($video);
                $addCount++;
            }
        }

        if ($addCount >= 1) {
            $message = 'Ran YouTube fetch. Added ' . $addCount . ' new-to-me videos.';
            $utilityModel->sendLog($message);
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
        $utilityModel = new UtilityModels();
        $now          = date('Y-m-d H:i:s');
        helper('youtube');

        $sql    = "SELECT * FROM aggro_videos WHERE video_date_uploaded <= DATE_SUB('" . $now . "',INTERVAL 31 DAY) AND flag_archive=0 AND flag_bad=0 AND video_duration=0 AND video_type='youtube' LIMIT 10";
        $query  = $this->db->query($sql);
        $update = count($query->getResultArray());

        if ($update > 0) {
          $results = $query->getResult();
          foreach ($results as $result) {
            $videoDuration = youtube_get_duration($result->video_id);
            $sql   = "UPDATE aggro_videos SET video_duration = " . $videoDuration . " WHERE video_id = '" . $result->video_id . "'";
            $query = $this->db->query($sql);
          }
        }

        $message = $update . ' video durations fetched.';
        $utilityModel->sendLog($message);

        return true;
    }

}
