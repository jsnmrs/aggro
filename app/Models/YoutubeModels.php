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
        $this->aggroModel   = $aggroModel ?? new AggroModels();
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
                $video = $this->ingestMeta($item, $currentVideoId);
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
                $video = $this->ingestMeta($item, $currentVideoId);
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
     * Build the row for a video arriving from a feed.
     *
     * The /shorts/ probe decides the Short flag. Nothing else beyond the
     * feed item and oEmbed is read.
     *
     * @param object $item
     *                        Feed item.
     * @param string $videoId
     *                        Video id.
     *
     * @return array
     *               Video metadata for insert.
     */
    private function ingestMeta(object $item, string $videoId): array
    {
        return youtube_parse_meta($item, null, $this->fetchShortForIngest($videoId));
    }

    /**
     * Check for a Short at ingest, retrying once on an inconclusive answer.
     *
     * When neither probe can answer, the video is stored as a regular
     * video, so a flaky probe makes a Short show rather than making a
     * video vanish.
     *
     * @param string $videoId
     *                        Video id.
     *
     * @return bool
     *              Video is a Short.
     */
    protected function fetchShortForIngest($videoId): bool
    {
        $short = $this->fetchShort($videoId);

        if ($short === null) {
            $this->sleepBetweenFetches();
            $short = $this->fetchShort($videoId);
        }

        if ($short === null) {
            log_message('warning', 'Shorts check for ' . $videoId . ' was inconclusive. Stored as a regular video.');

            return false;
        }

        return $short;
    }

    /**
     * Pause between /shorts/ probes so ingest stays polite.
     *
     * Extracted so tests can override it.
     */
    protected function sleepBetweenFetches(): void
    {
        $delay = config('Storage')->shortRequestDelay;

        if ($delay > 0) {
            sleep($delay);
        }
    }

    /**
     * Check whether a single video is a Short.
     *
     * Wraps the helper so tests can drive the outcome without network access.
     *
     * @param string $videoId
     *                        Video id.
     *
     * @return bool|null
     *                   True for a Short, false for a regular video, or null
     *                   when the check is inconclusive.
     */
    protected function fetchShort($videoId): ?bool
    {
        helper('youtube');

        return youtube_get_short($videoId);
    }
}
