<?php

namespace App\Controllers;

use App\Models\NewsModels;

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

    $data['sites'] = $newsModel->getSites();
    echo view('xml/opml', $data);
  }

  /**
   * Video RSS feed.
   */
  public function videofeed() {
    echo "rss";
  }

  /**
   * Directory RSS feed.
   */
  public function newsfeed() {
    $newsModel = new NewsModels();

    $data['updates'] = $newsModel->getSitesRecent();
    echo view('xml/feed', $data);
  }

}
