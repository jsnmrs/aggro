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

    return view('about', $data);
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

      return view('sites', $data);
    }

    $data['build'] = $newsModel->getSite($slug);

    if (!empty($data['build'])) {
      $data['feedfetch'] = fetch_feed($data['build']['site_feed'], 0, 3600);
      $newsModel->updateFeed($slug, $data['feedfetch']);

      return view('site', $data);
    }

    return $this->error404();
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
  public function video($slug = NULL) {
    $data = [
      'title' => 'Videos',
      'slug' => 'video',
    ];
    $aggroModel = new AggroModels();

    if ($slug == NULL || $slug == "recent") {
      $data['page'] = 1;

      if ($this->request->uri->getTotalSegments() == 3 &&
        is_numeric($this->request->uri->getSegment(3))) {
        $data['page'] = intval(esc($this->request->uri->getSegment(3)));
      }

      $data['sort'] = 'recent';
      $data['range'] = 'year';
      $data['perpage'] = 24;
      $data['offset'] = ($data['page'] - 1) * $data['perpage'];
      $data['total'] = $aggroModel->getVideosTotal();
      $data['endpage'] = ceil($data['total'] / $data['perpage']);

      if ($data['page'] > $data['endpage']) {
        return $this->error404();
      }

      $data['build'] = $aggroModel->getVideos($data['sort'], $data['range'], $data['perpage'], $data['offset']);

      return view('videos', $data);
    }

    $data['build'] = $aggroModel->getVideo(esc($slug));

    if (!empty($data['build'])) {
      return view('video', $data);
    }

    return $this->error404();
  }

}
