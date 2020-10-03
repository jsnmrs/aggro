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
   * Update featured/stream pages.
   *
   * Set cron to run every 5 minutes.
   */
  public function news($slug = NULL) {
    helper('aggro');
    $newsModel = new NewsModels();

    if ($slug == "clean") {
      $status = $newsModel->featuredCleaner();
      if (isset($status)) {
        echo $status . " featured news stories cleared.";
      }
      log_message('error', 'featured clean failed');
    }

    if ($slug == "cc") {
      $status = clean_feed_cache();
      if (isset($status)) {
        echo $status . " feed caches cleared.";
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
    $aggroModel = new AggroModels();

    $status = $aggroModel->twitterPush();
    if ($status === TRUE) {
      echo "Pushed any new videos to twitter.";
    }
    log_message('error', 'Twitter check failed');
  }

  /**
   * Vimeo video fetcher.
   *
   * Set cron to run every 5 minutes.
   */
  public function vimeo() {
    // Get Vimeo channels that haven't been updated in XX minutes.
    // Loop feed to find any video ids we don't have.
    // Add metadata for new videos to DB.
    // If upload date is > XX days mark video as archived.
  }

  /**
   * YouTube video fetcher.
   *
   * Set cron to run every 5 minutes.
   */
  public function youtube() {
    // Get YouTube channels that haven't been updated in XX minutes.
    // Loop feed to find any video ids we don't have.
    // Add metadata for new videos to DB.
    // If upload date is > XX days mark video as archived.
  }

}
