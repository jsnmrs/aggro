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
}
