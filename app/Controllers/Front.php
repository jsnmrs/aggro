<?php

namespace App\Controllers;

use App\Models\NewsModels;
use App\Models\UtilityModels;

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

    $newsModel = new NewsModels();
    $utilityModel = new UtilityModels();

    if ($slug == NULL) {
      $data['site'] = $newsModel->getAllSites();
      echo view('sites', $data);
    }

    if ($slug != NULL) {
      $data['site'] = $newsModel->getSingleSite($slug);

      if (!empty($data['site'])) {
        $data['feedfetch'] = $utilityModel->fetchFeed($data['site']['site_feed'], 0);
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
