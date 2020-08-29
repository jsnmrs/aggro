<?php

namespace App\Controllers;

use App\Models\NewsModels;
use App\Models\AggroModels;

/**
 * All feed-based contollers.
 */
class Feed extends BaseController {

  /**
   * Index -> RSS feed.
   */
  public function index() {
    $this->newsfeed();
  }

  /**
   * OPML generator.
   */
  public function opml() {
    $newsModel = new NewsModels();

    $data['build'] = $newsModel->getSites();
    echo view('xml/opml', $data);
  }

  /**
   * Video RSS feed.
   */
  public function videofeed() {
    $aggroModel = new AggroModels();

    $data['build'] = $aggroModel->getVideos('recent', 'month', 25, 0);
    echo view('xml/rss', $data);
  }

  /**
   * Directory RSS feed.
   */
  public function newsfeed() {
    $newsModel = new NewsModels();

    $data['build'] = $newsModel->getSitesRecent();
    echo view('xml/feed', $data);
  }

}
