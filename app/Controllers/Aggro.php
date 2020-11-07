<?php

namespace App\Controllers;

use App\Models\AggroModels;
use App\Models\NewsModels;
use App\Models\UtilityModels;
use App\Models\VimeoModels;
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

      $status = $aggroModel->cleanThumbs();
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
  public function vimeo($videoID = NULL) {
    helper(['aggro', 'vimeo']);
    $aggroModel = new AggroModels();
    $vimeoModel = new VimeoModels();

    if (gate_check()) {
      if ($videoID == NULL) {
        $data['stale'] = $aggroModel->getChannels(30, "vimeo", 1);

        if ($data['stale'] != FALSE) {
          foreach ($data['stale'] as $channel) {
            $data['feed'] = vimeo_get_feed($channel->source_channel_id);
            $data['number_added'] = $vimeoModel->parseChannel($data['feed']);

            echo "Added " . $data['number_added'] . " videos from " . $channel->source_name . ".<br>";

            if ($data['feed'] != FALSE || empty($data['feed'])) {
              $aggroModel->updateChannel($channel->source_slug);
            }
          }
        }
      }

      if ($videoID !== NULL) {
        $videoID = esc($videoID);
        if (!$aggroModel->checkVideo($videoID)) {
          $request = "https://vimeo.com/api/v2/video/" . $videoID . ".json";
          $result = fetch_url($request, 'json', 0);
          foreach ($result as $item) {
            $sourceID = str_replace('https://vimeo.com/', '', $item->user_url);
          }

          $data['feed'] = vimeo_get_feed($sourceID);
          $data['number_added'] = $vimeoModel->parseChannel($data['feed'], $videoID);

          echo "Added https://vimeo.com/" . $videoID . " from " . $sourceID . ".<br>";
        }
      }
    }
  }

  /**
   * YouTube video fetcher.
   *
   * Set cron to run every 5 minutes.
   */
  public function youtube($videoID = NULL) {
    helper(['aggro', 'youtube']);
    $aggroModel = new AggroModels();
    $youtubeModel = new YoutubeModels();

    if (gate_check()) {
      if ($videoID == NULL) {
        $data['stale'] = $aggroModel->getChannels(30, "youtube", 5);

        if ($data['stale'] != FALSE) {
          foreach ($data['stale'] as $channel) {
            $data['feed'] = youtube_get_feed($channel->source_channel_id);
            $data['number_added'] = $youtubeModel->parseChannel($data['feed']);

            echo "Added " . $data['number_added'] . " videos from " . $channel->source_name . ".<br>";

            if ($data['feed'] != FALSE || empty($data['feed'])) {
              $aggroModel->updateChannel($channel->source_slug);
            }
          }
        }
      }

      if ($videoID !== NULL) {
        $videoID = esc($videoID);
        if (!$aggroModel->checkVideo($videoID)) {
          $oEmbed = "https://www.youtube.com/oembed?format=xml&url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D" . $videoID;
          $result = fetch_url($oEmbed, 'simplexml', 1);

          if (strpos($result->author_url, 'channel/') !== FALSE) {
            $sourceID = str_replace('https://www.youtube.com/channel/', '', $result->author_url);
          }

          if (strpos($result->author_url, 'user/') !== FALSE) {
            $sourceID = str_replace('https://www.youtube.com/user/', '', $result->author_url);
          }

          $data['feed'] = youtube_get_feed($sourceID);
          $data['number_added'] = $youtubeModel->parseChannel($data['feed'], $videoID);

          echo "Added https://www.youtube.com/watch?v=" . $videoID . " from " . $sourceID . ".<br>";
        }
      }
    }
  }

}
