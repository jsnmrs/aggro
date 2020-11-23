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
    $data = ['title' => 'Log'];
    $utilityModel = new UtilityModels();

    if (!gate_check()) {
      return FALSE;
    }

    if ($slug == NULL) {
      $data['build'] = $utilityModel->getLog();
      return view('textlog', $data);
    }

    if ($slug == "clean") {
      $data['build'] = $utilityModel->cleanLog();
      return $this->response->redirect('/aggro/log');
    }

    if ($slug == "error") {
      $data['title'] = "Error log";
      $data['build'] = fetch_error_logs();
      return view('textlog', $data);
    }

    if ($slug == "errorclean") {
      $data['build'] = clean_error_logs();
      return $this->response->redirect('/aggro/log/error');
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

    if (!gate_check()) {
      return FALSE;
    }

    if ($slug == NULL) {
      $data['build'] = $newsModel->featuredBuilder();
      return "Featured page built.";
    }

    if ($slug == "clean") {
      $data['build'] = $newsModel->featuredCleaner();
      return "Featured news stories cleared.";
    }

    if ($slug == "cc") {
      $data['build'] = clean_feed_cache();
      return "Feed caches cleared.";
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
        echo "\nOld videos archived.\n";
      }

      $status = $aggroModel->cleanThumbs();
      if ($status === TRUE) {
        echo "\nThumbnails cleaned up.\n";
      }
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
        echo "\nPushed all new videos to twitter.\n";
      }
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

    if (!gate_check()) {
      return FALSE;
    }

    if ($videoID == NULL) {
      $data['stale'] = $aggroModel->getChannels(30, "vimeo", 5);

      if ($data['stale'] == FALSE) {
        return FALSE;
      }

      foreach ($data['stale'] as $channel) {
        $data['feed'] = vimeo_get_feed($channel->source_channel_id);
        $data['number_added'] = $vimeoModel->parseChannel($data['feed']);
        echo "\nAdded " . $data['number_added'] . " videos from " . $channel->source_name . ".\n";
        $aggroModel->updateChannel($channel->source_slug);
      }

      return TRUE;
    }

    $videoID = esc($videoID);

    if (!$aggroModel->checkVideo($videoID)) {
      $request = "https://vimeo.com/api/v2/video/" . $videoID . ".json";
      $result = fetch_url($request, 'json', 0);
      $sourceID = str_replace('https://vimeo.com/', '', $result[0]->user_url);

      $data['feed'] = vimeo_get_feed($sourceID);
      $data['number_added'] = $vimeoModel->parseChannel($data['feed'], $videoID);

      echo "\nAdded https://vimeo.com/" . $videoID . " from " . $sourceID . ".\n";

      return TRUE;
    }

    return FALSE;
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

            echo "\nAdded " . $data['number_added'] . " videos from " . $channel->source_name . ".\n";

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

          echo "\nAdded https://www.youtube.com/watch?v=" . $videoID . " from " . $sourceID . ".\n";
        }
      }
    }
  }

}
