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
                $video = youtube_parse_meta($item, null, $this->fetchDurationForIngest($currentVideoId));
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
                $video = youtube_parse_meta($item, null, $this->fetchDurationForIngest($currentVideoId));
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
     * Newest videos are filled first, since those are the ones the front
     * page shows. The batch is capped and requests are spaced out to stay
     * polite to the watch page. Write count of updated videos, and any
     * backlog left over, to log.
     *
     * @return bool
     *              Batch complete.
     *
     * @see sendLog()
     */
    public function getDuration()
    {
        helper('youtube');

        $storageConfig = config('Storage');

        $query = $this->db->table('aggro_videos')
            ->where('flag_archive', 0)
            ->where('flag_bad', 0)
            ->where('video_duration', 0)
            ->where('video_type', 'youtube')
            ->orderBy('aggro_date_added', 'DESC')
            ->limit($storageConfig->durationBatchSize)
            ->get();

        if ($query === false) {
            return false;
        }

        $updated = 0;

        foreach ($query->getResult() as $index => $result) {
            if ($index > 0) {
                $this->sleepBetweenFetches();
            }

            $unavailable   = false;
            $videoDuration = $this->fetchDuration($result->video_id, $unavailable);

            if ($videoDuration !== false && is_numeric($videoDuration)) {
                $this->videoRepository->updateVideoDuration($result->video_id, $videoDuration);
                $updated++;

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

        $this->utilityModel->sendLog($this->durationLogMessage($updated));

        return true;
    }

    /**
     * Describe a duration run, noting any backlog the batch did not reach.
     *
     * @param int $updated
     *                     Number of durations written this run.
     *
     * @return string
     *                Log message.
     */
    private function durationLogMessage($updated)
    {
        $remaining = $this->db->table('aggro_videos')
            ->where('flag_archive', 0)
            ->where('flag_bad', 0)
            ->where('video_duration', 0)
            ->where('video_type', 'youtube')
            ->countAllResults();

        $message = $updated . ' video durations fetched';

        if ($remaining > 0) {
            $message .= ', ' . $remaining . ' still waiting';
        }

        return $message . '.';
    }

    /**
     * Fetch a duration at ingest, retrying once on an ambiguous failure.
     *
     * A single flaky watch-page request should not hide a video until the
     * nightly job reaches it. A video the source reports as unavailable is
     * not retried, since it will never yield a duration.
     *
     * @param string $videoId
     *                        Video id.
     *
     * @return false|string
     *                      Video duration, or false when both attempts fail.
     */
    protected function fetchDurationForIngest($videoId)
    {
        $unavailable = false;
        $duration    = $this->fetchDuration($videoId, $unavailable);

        if ($duration !== false || $unavailable) {
            return $duration;
        }

        $this->sleepBetweenFetches();

        return $this->fetchDuration($videoId, $unavailable);
    }

    /**
     * Pause between watch-page fetches so the job stays polite.
     *
     * Extracted so tests can override it.
     */
    protected function sleepBetweenFetches(): void
    {
        $delay = config('Storage')->durationRequestDelay;

        if ($delay > 0) {
            sleep($delay);
        }
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
