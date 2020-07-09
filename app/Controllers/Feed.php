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
    $this->rssfeed();
  }

  /**
   * OPML generator.
   */
  public function opml() {
    $newsModel = new NewsModels();

    $data['sites'] = $newsModel->getAllSites();
    echo view('opml', $data);
  }

  /**
   * RSS feed.
   */
  public function rss() {
  }

  /**
   * RSS feed.
   */
  public function rssfeed() {
  }

  /**
   * Video RSS feed.
   */
  public function videofeed() {
  }

}
