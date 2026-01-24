<?php

namespace App\Controllers;

use App\Models\AggroModels;
use App\Models\NewsModels;
use App\Models\UtilityModels;
use App\Models\VimeoModels;
use App\Models\YoutubeModels;
use CodeIgniter\CodeIgniter;

/**
 * All aggro controllers.
 */
class Aggro extends BaseController
{
    /**
     * Aggro front.
     */
    public function getIndex()
    {
        echo '<h1 style="color:#005600;font-size:15vw;line-height:.9;font-family:sans-serif;letter-spacing:-.05em;">running cron all day.</h1>';
    }

    /**
     * Aggro info.
     *
     * @return void
     */
    public function getInfo()
    {
        // Prevent caching to ensure fresh deployment info
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Thu, 1 Jan 1970 00:00:00 GMT');

        echo '<h1 style="color:#005600;font-size:15vw;line-height:.9;font-family:sans-serif;letter-spacing:-.05em;">CI ' . CodeIgniter::CI_VERSION . '<br>PHP ' . PHP_VERSION . '</h1>';
    }

    /**
     * Show aggro log.
     */
    public function getLog()
    {
        helper('aggro');
        $data         = ['title' => 'Log'];
        $utilityModel = new UtilityModels();

        if (! gate_check()) {
            return false;
        }

        $data['build'] = $utilityModel->getLog();

        return view('textlog', $data);
    }

    /**
     * Clean aggro log.
     */
    public function getLogClean()
    {
        helper('aggro');
        $utilityModel = new UtilityModels();

        if (! gate_check()) {
            return false;
        }

        $utilityModel->cleanLog();

        return $this->response->redirect('/aggro/log');
    }

    /**
     * Show aggro error log.
     */
    public function getLogError()
    {
        helper('aggro');
        $data = ['title' => 'Error log'];

        if (! gate_check()) {
            return false;
        }

        $data['title']            = 'Error log';
        $data['build']            = fetch_error_logs();
        $data['system_generated'] = true;

        return view('textlog', $data);
    }

    /**
     * Clean aggro error logs.
     */
    public function getLogErrorClean()
    {
        helper('aggro');

        if (! gate_check()) {
            return false;
        }

        clean_error_logs();

        return $this->response->redirect('/aggro/log/error');
    }

    /**
     * Update featured/stream pages.
     *
     * Set cron to run every 5 minutes.
     *
     * @param string|null $slug
     */
    public function getNews(?string $slug = null)
    {
        helper('aggro');
        $newsModel = new NewsModels();

        if (! gate_check()) {
            return false;
        }

        if ($slug === null) {
            $newsModel->featuredBuilder();

            return 'Featured page built.';
        }

        if ($slug === 'clean') {
            $newsModel->featuredCleaner();

            return 'Featured news stories cleared.';
        }

        if ($slug === 'cc') {
            clean_feed_cache();

            return 'Feed caches cleared.';
        }
    }

    /**
     * Clear featured/stream cache.
     */
    public function getNewsCache()
    {
        helper('aggro');

        if (! gate_check()) {
            return false;
        }

        clean_feed_cache();

        return 'Feed caches cleared.';
    }

    /**
     * Clean featured/stream pages.
     */
    public function getNewsClean()
    {
        helper('aggro');
        $newsModel = new NewsModels();

        if (! gate_check()) {
            return false;
        }

        $newsModel->featuredCleaner();

        return 'Featured news stories cleared.';
    }

    /**
     * Update archive old videos, run cleanup.
     *
     * Set cron to run every 60 minutes.
     */
    public function getSweep()
    {
        helper('aggro');
        $aggroModel = new AggroModels();

        if (! gate_check()) {
            return false;
        }

        if ($aggroModel->archiveVideos()) {
            echo "\nOld videos archived.\n";
        }

        if ($aggroModel->checkThumbs()) {
            echo "\nThumbnails checked.\n";
        }

        if ($aggroModel->cleanThumbs()) {
            echo "\nThumbnails cleaned up.\n";
        }

        return true;
    }

    /**
     * Update duration value for videos.
     */
    public function getYouTubeDuration()
    {
        helper(['aggro', 'youtube']);
        $youtubeModel = new YoutubeModels();

        if (! gate_check()) {
            return false;
        }
        if ($youtubeModel->getDuration()) {
            echo "\nDurations fetched.\n";
        }

        return true;
    }

    /**
     * Vimeo video fetcher.
     *
     * Set cron to run every 5 minutes.
     *
     * @param string|null $videoID
     */
    public function getVimeo(?string $videoID = null)
    {
        helper(['aggro', 'vimeo']);
        $aggroModel = new AggroModels();
        $vimeoModel = new VimeoModels();

        if (! gate_check()) {
            return false;
        }

        if ($videoID === null) {
            $data['stale'] = $aggroModel->getChannels('30', 'vimeo', '5');

            if ($data['stale'] === false || empty($data['stale'])) {
                return false;
            }

            foreach ($data['stale'] as $channel) {
                $data['feed']         = vimeo_get_feed($channel->source_channel_id);
                $data['number_added'] = $vimeoModel->parseChannel($data['feed']);
                echo "\nAdded " . $data['number_added'] . ' videos from ' . $channel->source_name . ".\n";
                $aggroModel->updateChannel($channel->source_slug);
            }

            return true;
        }

        helper('html');

        // Validate Vimeo video ID format
        if (! $this->validateVimeoVideoId($videoID)) {
            log_message('warning', 'Invalid Vimeo video ID format: ' . $videoID);

            return false;
        }

        if (! $aggroModel->checkVideo($videoID)) {
            $request              = 'https://vimeo.com/api/v2/video/' . $videoID . '.json';
            $result               = json_decode(fetch_url($request, 'json', '0'));
            $sourceID             = str_replace('https://vimeo.com/', '', $result[0]->user_url);
            $data['feed']         = vimeo_get_feed($sourceID);
            $data['number_added'] = $vimeoModel->searchChannel($data['feed'], $videoID);
            echo "\nAdded https://vimeo.com/" . $videoID . ' from ' . $sourceID . ".\n";

            return true;
        }

        return false;
    }

    /**
     * YouTube video fetcher.
     *
     * Set cron to run every 5 minutes.
     *
     * @param string|null $videoID
     */
    public function getYoutube(?string $videoID = null)
    {
        helper(['aggro', 'youtube']);
        $aggroModel   = new AggroModels();
        $youtubeModel = new YoutubeModels();

        if (! gate_check()) {
            return false;
        }

        if ($videoID === null) {
            $data['stale'] = $aggroModel->getChannels('30', 'youtube', '5');

            if ($data['stale'] === false || empty($data['stale'])) {
                return false;
            }

            foreach ($data['stale'] as $channel) {
                $data['feed']         = youtube_get_feed($channel->source_channel_id);
                $data['number_added'] = $youtubeModel->parseChannel($data['feed']);
                echo "\nAdded " . $data['number_added'] . ' videos from ' . $channel->source_name . ".\n";
                $aggroModel->updateChannel($channel->source_slug);
            }

            return true;
        }

        helper('html');

        // Validate YouTube video ID format
        if (! $this->validateYouTubeVideoId($videoID)) {
            log_message('warning', 'Invalid YouTube video ID format: ' . $videoID);

            return false;
        }

        if ($aggroModel->checkVideo($videoID)) {
            return false;
        }

        $sourceID = youtube_get_video_source($videoID);

        $data['feed'] = youtube_get_feed($sourceID);
        $youtubeModel->searchChannel($data['feed'], $videoID);
        echo "\nAdded https://www.youtube.com/watch?v=" . $videoID . ' from ' . $sourceID . ".\n";

        return true;
    }
}
