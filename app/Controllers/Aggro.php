<?php

namespace App\Controllers;

use App\Models\AggroModels;
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
  public function featured($slug = NULL) {
    $newsModel = new NewsModels();

    if ($slug == "clean") {
      $status = $newsModel->featuredCleaner();
      if ($status === TRUE) {
        echo "Featured news cleaned.";
      }
      log_message('error', 'featured clean failed');
    }

    if ($slug == NULL) {
      $status = $newsModel->featuredBuilder();
      if ($status === TRUE) {
        echo "Featured page built.";
      }
      log_message('error', 'featured build failed');
    }
  }

  /**
   * Show aggro log.
   */
  public function log($slug = NULL) {
    helper("aggro");
    $data = [
      'title' => 'Log',
      'slug' => 'log',
    ];

    if ($slug == "error") {
      $data['build'] = fetch_error_logs();
      echo view('log', $data);
    }

    if ($slug == NULL) {
      $utilityModel = new UtilityModels();
      $data['build'] = $utilityModel->getLog();
      echo view('log', $data);
    }
  }

  /**
   * Update archive old videos, run cleanup.
   *
   * Set cron to run every 60 minutes.
   */
  public function sweep() {
    $aggroModel = new AggroModels();

    $status = $aggroModel->archiveVideos();
    if ($status === TRUE) {
      echo "Old videos archived.";
    }
    log_message('error', 'Video archiving failed');
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
