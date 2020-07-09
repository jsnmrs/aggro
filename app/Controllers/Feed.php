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

    $data['sites'] = $newsModel->getAllSites();
    echo view('xml/opml', $data);
  }

  /**
   * Video RSS feed.
   */
  public function rss() {
    echo "rss";
  }

  /**
   * Directory RSS feed.
   */
  public function newsfeed() {
    $newsModel = new NewsModels();

    $data['updates'] = $newsModel->getAllUpdates();
    echo view('xml/feed', $data);
  }

}
