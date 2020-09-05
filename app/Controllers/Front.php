<?php

namespace App\Controllers;

use App\Models\AggroModels;
use App\Models\NewsModels;

/**
 * All front-end contollers.
 */
class Front extends BaseController {

  /**
   * Home -> featured page.
   */
  public function index() {
    $this->featured();
  }

  /**
   * About page.
   */
  public function about() {
    $data = [
      'title' => 'About',
      'slug' => 'about',
    ];

    echo view('about', $data);
  }

  /**
   * Error page.
   */
  public function error404() {
    $this->response->setStatusCode(404);

    $data = [
      'title' => 'Page not found',
      'slug' => '404',
    ];

    echo view('error', $data);
  }

  /**
   * Featured page.
   */
  public function featured() {
    $data = [
      'title' => 'Featured',
      'slug' => 'featured',
    ];

    $newsModel = new NewsModels();

    $data['build'] = $newsModel->featuredPage();
    echo view('featured', $data);
  }

  /**
   * Outgoing links.
   */
  public function out() {
  }

  /**
   * Sites page.
   */
  public function sites($slug = NULL) {
    helper('aggro');
    $data = [
      'title' => 'Directory',
      'slug' => 'sites',
    ];

    $newsModel = new NewsModels();

    if ($slug == NULL) {
      $data['build'] = $newsModel->getSites();
      echo view('sites', $data);
    }

    if ($slug != NULL) {
      $data['build'] = $newsModel->getSite($slug);

      if (!empty($data['build'])) {
        $data['feedfetch'] = fetch_feed($data['build']['site_feed'], 0, 3600);
        $newsModel->updateFeed($slug, $data['feedfetch']);
        echo view('site', $data);
      }

      if (empty($data['build'])) {
        $this->error404();
      }
    }
  }

  /**
   * Stream page.
   */
  public function stream() {
    $data = [
      'title' => 'Stream',
      'slug' => 'stream',
    ];

    $newsModel = new NewsModels();

    $data['build'] = $newsModel->streamPage();
    echo view('stream', $data);
  }

  /**
   * Submit page.
   */
  public function submit() {
    $data = [
      'title' => 'Submit',
      'slug' => 'submit',
    ];

    echo view('submit', $data);
  }

  /**
   * Video pages.
   */
  public function video() {
    $data = [
      'title' => 'Videos',
      'slug' => 'video',
    ];

    $aggroModel = new AggroModels();

    $totalSegments = $this->request->uri->getTotalSegments();

    $slug = esc($this->request->uri->getSegment(2));
    if ($totalSegments == 3) {
      $page = esc($this->request->uri->getSegment(3));
    }

    if ($slug != NULL && $slug != "recent") {
      $data['build'] = $aggroModel->getVideo($slug);

      if (!empty($data['build'])) {
        echo view('video', $data);
      }

      if (empty($data['build'])) {
        $this->error404();
      }
    }

    if ($slug == NULL || $slug == "recent") {
      if (!isset($page) || $page == 0) {
        $data['page'] = 1;
      }

      if (isset($page) && is_numeric($page)) {
        $data['page'] = intval($page);
      }

      if ($data['page']) {
        $data['sort'] = 'recent';
        $data['range'] = 'year';
        $data['perpage'] = 24;
        $data['offset'] = ($data['page'] - 1) * $data['perpage'];
        $data['total'] = $aggroModel->getVideosTotal();
        $data['endpage'] = ceil($data['total'] / $data['perpage']);

        if ($data['page'] > $data['endpage']) {
          $this->error404();
        }

        if ($data['page'] <= $data['endpage']) {
          $data['build'] = $aggroModel->getVideos($data['sort'], $data['range'], $data['perpage'], $data['offset']);
          echo view('videos', $data);
        }
      }

      if (!$data['page']) {
        $this->error404();
      }
    }
  }

}
