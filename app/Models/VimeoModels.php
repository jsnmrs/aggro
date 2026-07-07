<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All Vimeo interactions with aggro_* tables.
 */
class VimeoModels extends Model
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
     * Search Vimeo feed for specific video.
     *
     * @param mixed  $feed
     *                        Fetched Vimeo feed.
     * @param string $videoId
     *                        Video ID to look for.
     *
     * @return bool
     *              Video added.
     */
    public function searchChannel($feed, $videoId)
    {
        helper('vimeo');

        if ($feed === false || (! is_array($feed) && ! is_object($feed))) {
            if ($feed !== false) {
                log_message('error', 'VimeoModels::searchChannel received invalid feed data: ' . gettype($feed));
            }

            return false;
        }

        foreach ($feed as $item) {
            // Ensure item is an object before processing
            if (! is_object($item) || ! isset($item->id)) {
                continue;
            }

            if ($videoId === $item->id && ! $this->aggroModel->checkVideo($item->id)) {
                $video = vimeo_parse_meta($item);
                $this->aggroModel->addVideo($video);

                return true;
            }
        }

        return false;
    }

    /**
     * Parse Vimeo feed for videos.
     *
     * @param mixed $feed
     *                    Fetched Vimeo feed.
     *
     * @return false|int
     *                   Number of videos added or false on error.
     */
    public function parseChannel($feed)
    {
        helper('vimeo');
        $addCount = 0;

        if ($feed === false || (! is_array($feed) && ! is_object($feed))) {
            if ($feed !== false) {
                log_message('error', 'VimeoModels::parseChannel received invalid feed data: ' . gettype($feed));
            }

            return false;
        }

        foreach ($feed as $item) {
            // Ensure item is an object before processing
            if (! is_object($item) || ! isset($item->id)) {
                log_message('warning', 'VimeoModels::parseChannel skipping invalid item');

                continue;
            }

            if (! $this->aggroModel->checkVideo($item->id)) {
                $video = vimeo_parse_meta($item);
                $this->aggroModel->addVideo($video);
                $addCount++;

                continue;
            }

            $this->updateExistingVideoPlays($item);
        }

        if ($addCount >= 1) {
            $message = 'Ran Vimeo fetch. Added ' . $addCount . ' new-to-me videos.';
            $this->utilityModel->sendLog($message);
        }

        return $addCount;
    }

    /**
     * Refresh stored play count for an existing video from feed data.
     *
     * Vimeo owners can hide stats; without the field no update happens.
     *
     * @param object $item
     *                     Feed item.
     *
     * @return void
     */
    private function updateExistingVideoPlays($item)
    {
        if (isset($item->stats_number_of_plays)) {
            $this->aggroModel->setVideoPlays($item->id, (int) $item->stats_number_of_plays);
        }
    }
}
