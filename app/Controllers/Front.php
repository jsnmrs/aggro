<?php

namespace App\Controllers;

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

    echo view('featured', $data);

    // $this->load->model('FrontendModels');
    // $data['nav'] = 'Featured';
    // $data['daily'] = $this->FrontendModels->getPopularLinks(1, 3);
    // $data['weekly'] = $this->FrontendModels->getPopularLinks(7, 3);
    // $data['top_videos'] = $this->FrontendModels->getAllVideos('popular', 'threedays', 4, 0, 'video');
    // $data['built'] = $this->FrontendModels->featuredPage();
    // $this->load->view('frontend/featured', $data);
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
    $data = [
      'title' => 'Directory',
      'slug' => 'sites',
    ];

    $model = new NewsModels();

    if ($slug == NULL) {
      echo view('sites', $data);
    }

    if ($slug != NULL) {
      $data['site'] = $model->getSingleSite($slug);

      if (!empty($data['site'])) {
        echo view('site', $data);
      }

      if (empty($data['site'])) {
        $this->error404();
      }
    }
  }

  /**
   * Featured page.
   */
  public function stream() {
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
  }

}
