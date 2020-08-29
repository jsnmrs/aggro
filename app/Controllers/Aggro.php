<?php

namespace App\Controllers;

use App\Models\NewsModels;
use App\Models\UtilityModels;

/**
 * All aggro contollers.
 */
class Aggro extends BaseController {

  /**
   * Aggro front.
   */
  public function index() {
    echo "<h1>running cron all day.</h1>";
  }

  /**
   * Update featured/stream pages.
   *
   * Set cron to run every 5 minutes.
   */
  public function featured() {
    $newsModel = new NewsModels();

    $status = $newsModel->featuredBuilder();
    if ($status === TRUE) {
      echo "Featured page built.";
    }
    log_message('error', 'featured build failed');
  }

  /**
   * Show aggro log.
   */
  public function log() {
    $data = [
      'title' => 'Log',
      'slug' => 'log',
    ];

    $utilityModel = new UtilityModels();

    $data['build'] = $utilityModel->getLog();
    echo view('log', $data);
  }

  /**
   * Update archive old videos, run cleanup.
   *
   * Set cron to run every 60 minutes.
   */
  public function sweep() {
  }

  /**
   * Tweeter.
   *
   * Set cron to run every 5 minutes.
   */
  public function twitter() {
  }

  /**
   * Vimeo video fetcher.
   *
   * Set cron to run every 5 minutes.
   */
  public function vimeo() {
  }

  /**
   * YouTube video fetcher.
   *
   * Set cron to run every 5 minutes.
   */
  public function youtube() {
  }

}
