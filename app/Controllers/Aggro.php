<?php

namespace App\Controllers;

use App\Models\AggroModels;
use App\Models\NewsModels;
use App\Models\UtilityModels;
use App\Models\YoutubeModels;

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
    $data = [
      'title' => 'Log',
      'slug' => 'log',
    ];

    if ($slug == "error") {
      $data['build'] = fetch_error_logs();
      echo view('log', $data);
    }

    if ($slug == "errorclean" && gate_check()) {
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
    $newsModel = new NewsModels();

    if ($slug == "clean" && gate_check()) {
      $status = $newsModel->featuredCleaner();
      if (isset($status)) {
        echo $status . " featured news stories cleared.";
      }
      log_message('error', 'featured clean failed');
    }

    if ($slug == "cc" && gate_check()) {
      $status = clean_feed_cache();
      if (isset($status)) {
        echo $status . " feed caches cleared.";
      }
      log_message('error', 'featured clean failed');
    }

    if ($slug == NULL && gate_check()) {
      $status = $newsModel->featuredBuilder();
      if ($status === TRUE) {
        echo "Featured page built.";
      }
      log_message('error', 'featured build failed');
    }

    if (!gate_check()) {
      echo "<h1 style=\"color:#005600;font-size:15vw;line-height:.9;font-family:sans-serif;letter-spacing:-.05em;\">Huey Lewis and the &hellip;</h1>";
    }
  }

  /**
   * Update archive old videos, run cleanup.
   *
   * Set cron to run every 60 minutes.
   */
  public function sweep() {
    helper('aggro');
    $aggroModel = new AggroModels();

    if (gate_check()) {
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
    helper('aggro');
    $aggroModel = new AggroModels();

    if (gate_check()) {
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
    helper(['aggro', 'youtube']);
    $aggroModel = new AggroModels();
    $youtubeModel = new YoutubeModels();

    if (gate_check()) {
      $data['stale'] = $aggroModel->getChannels(30, "youtube", 5);

      if ($data['stale'] != FALSE) {
        foreach ($data['stale'] as $channel) {

          if (substr($channel->source_channel_id, 0, 2) == "UC") {
            $data['feed'] = youtube_get_channel_feed($channel->source_channel_id);
          }

          if (substr($channel->source_channel_id, 0, 2) == "PL") {
            $data['feed'] = youtube_get_playlist_feed($channel->source_channel_id);
          }

          $data['number_added'] = $youtubeModel->parseChannel($data['feed']);

          echo "Added " . $data['number_added'] . " videos from " . $channel->source_name . ".<br>";

          if ($data['feed'] != FALSE || empty($data['feed'])) {
            $aggroModel->updateChannel($channel->source_slug);
          }
        }
      }
    }
  }

}
