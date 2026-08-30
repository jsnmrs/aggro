<?php

namespace App\Models;

use App\Repositories\VideoRepository;
use CodeIgniter\Model;

/**
 * All YouTube interactions with aggro_* tables.
 */
class YoutubeModels extends Model
{
    protected $aggroModel;
    protected $utilityModel;
    protected $videoRepository;

    public function __construct(?AggroModels $aggroModel = null, ?UtilityModels $utilityModel = null, ?VideoRepository $videoRepository = null)
    {
        parent::__construct();
        $this->aggroModel      = $aggroModel ?? new AggroModels();
        $this->utilityModel    = $utilityModel ?? new UtilityModels();
        $this->videoRepository = $videoRepository ?? new VideoRepository();
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

                continue;
            }

            $plays = youtube_parse_plays($item);
            if ($plays !== false) {
                $this->aggroModel->setVideoPlays($currentVideoId, (int) $plays);
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
                $unavailable   = false;
                $videoDuration = $this->fetchDuration($result->video_id, $unavailable);

                if ($videoDuration !== false && is_numeric($videoDuration)) {
                    $this->videoRepository->updateVideoDuration($result->video_id, $videoDuration);

                    continue;
                }

                if ($unavailable) {
                    $this->videoRepository->flagVideoBad($result->video_id);
                    $this->utilityModel->sendLog('Retired ' . $result->video_id . '. Source reports the video is unavailable.');
                    log_message('warning', 'Flagged video ' . $result->video_id . ' as bad — source reports it unavailable.');

                    continue;
                }

                $this->videoRepository->recordDurationIssue($result->video_id);
            }
        }

        $message = $update . ' video durations fetched.';
        $this->utilityModel->sendLog($message);

        return true;
    }

    /**
     * Fetch the duration for a single video.
     *
     * Wraps the helper so tests can drive the outcome without network access.
     *
     * @param string    $videoId
     *                                Video id.
     * @param bool|null &$unavailable
     *                                Optional. Populated with true when the source
     *                                reports the video as unwatchable.
     *
     * @param-out bool $unavailable
     *
     * @return false|string
     *                      Video duration, or false on error.
     */
    protected function fetchDuration($videoId, &$unavailable = null)
    {
        helper('youtube');

        return youtube_get_duration($videoId, $unavailable);
    }
}
