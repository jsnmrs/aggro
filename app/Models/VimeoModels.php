<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * All Vimeo interactions with aggro_* tables.
 */
class VimeoModels extends Model
{
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
        $aggroModel = new AggroModels();
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

            if ($videoId === $item->id && ! $aggroModel->checkVideo($item->id)) {
                $video = vimeo_parse_meta($item);
                $aggroModel->addVideo($video);

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
        $aggroModel   = new AggroModels();
        $utilityModel = new UtilityModels();
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

            if (! $aggroModel->checkVideo($item->id)) {
                $video = vimeo_parse_meta($item);
                $aggroModel->addVideo($video);
                $addCount++;
            }
        }

        if ($addCount >= 1) {
            $message = 'Ran Vimeo fetch. Added ' . $addCount . ' new-to-me videos.';
            $utilityModel->sendLog($message);
        }

        return $addCount;
    }
}
