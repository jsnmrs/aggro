<?php

namespace App\Controllers;

use App\Models\AggroModels;
use App\Models\NewsModels;

/**
 * All feed-based contollers.
 */
class Feed extends BaseController
{
    /**
     * Index -> RSS feed.
     */
    public function getIndex()
    {
        $this->getNewsfeed();
    }

    /**
     * OPML generator.
     */
    public function getOpml()
    {
        $newsModel = new NewsModels();

        $data['build'] = $newsModel->getSites();
        $this->response->setContentType('application/rss+xml');

        return view('xml/opml', $data);
    }

    /**
     * Video RSS feed.
     */
    public function getVideofeed()
    {
        $aggroModel = new AggroModels();

        $data['build'] = $aggroModel->getVideos('recent', 'month', 25, 0);
        $this->response->setContentType('application/rss+xml');

        return view('xml/rss', $data);
    }

    /**
     * Directory RSS feed.
     */
    public function getNewsfeed()
    {
        $newsModel = new NewsModels();

        $data['build'] = $newsModel->getSitesRecent();
        $this->response->setContentType('application/rss+xml');

        return view('xml/feed', $data);
    }
}
