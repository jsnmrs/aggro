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
        helper('vimeo');

        if ($feed === false) {
            return false;
        }

        foreach ($feed as $item) {
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
        helper('vimeo');
        $addCount = 0;

        if ($feed === false) {
            return false;
        }

        foreach ($feed as $item) {
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
