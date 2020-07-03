<?php

namespace App\Controllers;

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
