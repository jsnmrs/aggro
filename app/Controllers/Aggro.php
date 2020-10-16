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
    echo "<h1 style=\"color:#005600;font-size:15vw;line-height:.9;font-family:sans-serif;letter-spacing:-.05em;\">running cron all day.</h1>";
  }

  /**
   * Show aggro log.
   */
  public function log($slug = NULL) {
    helper("aggro");
    $request = \Config\Services::request();
    $data = [
      'title' => 'Log',
      'slug' => 'log',
    ];

    if ($slug == "error") {
      $data['build'] = fetch_error_logs();
      echo view('log', $data);
    }

    if ($slug == "errorclean" && $request->isCLI()) {
      $data['build'] = clean_error_logs();
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
    $request = \Config\Services::request();
    $newsModel = new NewsModels();

    if ($slug == "clean" && $request->isCLI()) {
      $status = $newsModel->featuredCleaner();
      if (isset($status)) {
        echo $status . " featured news stories cleared.";
      }
      log_message('error', 'featured clean failed');
    }

    if ($slug == "cc" && $request->isCLI()) {
      $status = clean_feed_cache();
      if (isset($status)) {
        echo $status . " feed caches cleared.";
      }
      log_message('error', 'featured clean failed');
    }

    if ($slug == NULL && $request->isCLI()) {
      $status = $newsModel->featuredBuilder();
      if ($status === TRUE) {
        echo "Featured page built.";
      }
      log_message('error', 'featured build failed');
    }

    if (!$request->isCLI()) {
      echo "<h1 style=\"color:#005600;font-size:15vw;line-height:.9;font-family:sans-serif;letter-spacing:-.05em;\">Huey Lewis and the &hellip;</h1>";
    }
  }

  /**
   * Update archive old videos, run cleanup.
   *
   * Set cron to run every 60 minutes.
   */
  public function sweep() {
    $request = \Config\Services::request();
    $aggroModel = new AggroModels();

    if ($request->isCLI()) {
      $status = $aggroModel->archiveVideos();
      if ($status === TRUE) {
        echo "Old videos archived.";
      }
      log_message('error', 'Video archiving failed');
    }
  }

  /**
   * Tweeter.
   *
   * Set cron to run every 5 minutes.
   */
  public function twitter() {
    $request = \Config\Services::request();
    $aggroModel = new AggroModels();

    if ($request->isCLI()) {
      $status = $aggroModel->twitterPush();
      if ($status === TRUE) {
        echo "Pushed any new videos to twitter.";
      }
      log_message('error', 'Twitter check failed');
    }
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
