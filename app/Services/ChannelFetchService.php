<?php

namespace App\Services;

use App\Models\AggroModels;
use App\Models\VimeoModels;
use App\Models\YoutubeModels;

/**
 * Service for fetching videos from stale channels.
 */
class ChannelFetchService
{
    protected AggroModels $aggroModel;
    protected VimeoModels $vimeoModel;
    protected YoutubeModels $youtubeModel;

    public function __construct()
    {
        $this->aggroModel   = new AggroModels();
        $this->vimeoModel   = new VimeoModels();
        $this->youtubeModel = new YoutubeModels();
    }

    /**
     * Process stale Vimeo channels.
     */
    public function processStaleVimeoChannels(array $stale): void
    {
        helper(['aggro', 'vimeo']);

        foreach ($stale as $channel) {
            $feed = vimeo_get_feed($channel->source_channel_id);

            if ($feed === false) {
                $this->aggroModel->incrementChannelFailCount($channel->source_slug);
                $this->aggroModel->updateChannel($channel->source_slug);

                continue;
            }

            $this->aggroModel->resetChannelFailCount($channel->source_slug);
            $numberAdded = $this->vimeoModel->parseChannel($feed);
            echo "\nAdded " . $numberAdded . ' videos from ' . $channel->source_name . ".\n";
            $this->aggroModel->updateChannel($channel->source_slug);
        }
    }

    /**
     * Process stale YouTube channels.
     */
    public function processStaleYoutubeChannels(array $stale): void
    {
        helper(['aggro', 'youtube']);

        foreach ($stale as $channel) {
            $feed = youtube_get_feed($channel->source_channel_id);

            if ($feed === false || $feed->error()) {
                $this->aggroModel->incrementChannelFailCount($channel->source_slug);
                $this->aggroModel->updateChannel($channel->source_slug);

                continue;
            }

            $this->aggroModel->resetChannelFailCount($channel->source_slug);
            $numberAdded = $this->youtubeModel->parseChannel($feed);
            echo "\nAdded " . $numberAdded . ' videos from ' . $channel->source_name . ".\n";
            $this->aggroModel->updateChannel($channel->source_slug);
        }
    }

    /**
     * Add a single Vimeo video by looking it up in its uploader's feed.
     *
     * @return bool|null
     *                   True when the video was added, false when it is not in
     *                   the feed, or null when the video lookup fails.
     */
    public function addVimeoVideo(string $videoID): ?bool
    {
        $sourceID = $this->fetchVimeoSource($videoID);

        if ($sourceID === false) {
            log_message('error', 'Vimeo API returned no usable data for video ID: ' . $videoID);
            echo "\nCould not look up https://vimeo.com/" . $videoID . ".\n";

            return null;
        }

        $feed = $this->fetchVimeoFeed($sourceID);

        if (! $this->vimeoModel->searchChannel($feed, $videoID)) {
            echo "\nhttps://vimeo.com/" . $videoID . ' was not found in the feed for ' . $sourceID . ".\n";

            return false;
        }

        echo "\nAdded https://vimeo.com/" . $videoID . ' from ' . $sourceID . ".\n";

        return true;
    }

    /**
     * Add a single YouTube video by looking it up in its channel's feed.
     *
     * @return bool|null
     *                   True when the video was added, false when it is not in
     *                   the feed, or null when the channel lookup fails.
     */
    public function addYoutubeVideo(string $videoID): ?bool
    {
        $sourceID = $this->fetchYoutubeSource($videoID);

        if ($sourceID === false) {
            log_message('warning', 'YouTube source lookup failed for video ID: ' . $videoID);
            echo "\nCould not look up https://www.youtube.com/watch?v=" . $videoID . ".\n";

            return null;
        }

        $feed = $this->fetchYoutubeFeed($sourceID);

        if (! $this->youtubeModel->searchChannel($feed, $videoID)) {
            echo "\nhttps://www.youtube.com/watch?v=" . $videoID . ' was not found in the feed for ' . $sourceID . ".\n";

            return false;
        }

        echo "\nAdded https://www.youtube.com/watch?v=" . $videoID . ' from ' . $sourceID . ".\n";

        return true;
    }

    /**
     * Look up the uploader of a Vimeo video.
     *
     * Wraps the helper so tests can drive the outcome without network access.
     *
     * @return false|string
     *                      Uploader's source ID, or false when the lookup fails.
     */
    protected function fetchVimeoSource(string $videoID)
    {
        helper('aggro');

        $request = 'https://vimeo.com/api/v2/video/' . $videoID . '.json';
        $result  = fetch_url($request, 'json', 0);

        if ($result === false || empty($result) || ! isset($result[0]->user_url)) {
            return false;
        }

        return str_replace('https://vimeo.com/', '', $result[0]->user_url);
    }

    /**
     * Fetch a Vimeo uploader's feed.
     *
     * Wraps the helper so tests can drive the outcome without network access.
     *
     * @return false|object
     *                      JSON object, or false on error.
     */
    protected function fetchVimeoFeed(string $sourceID)
    {
        helper('vimeo');

        return vimeo_get_feed($sourceID);
    }

    /**
     * Look up the channel of a YouTube video.
     *
     * Wraps the helper so tests can drive the outcome without network access.
     *
     * @return false|string
     *                      Channel's source ID, or false when the lookup fails.
     */
    protected function fetchYoutubeSource(string $videoID)
    {
        helper('youtube');

        return youtube_get_video_source($videoID);
    }

    /**
     * Fetch a YouTube channel's feed.
     *
     * Wraps the helper so tests can drive the outcome without network access.
     *
     * @return object
     *                SimplePie RSS object.
     */
    protected function fetchYoutubeFeed(string $sourceID)
    {
        helper('youtube');

        return youtube_get_feed($sourceID);
    }
}
