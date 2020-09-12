<?php

namespace App\Controllers;

/**
 * Class BaseController.
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController.
 *
 * For security be sure to declare any new methods as protected or private.
 *
 * @package CodeIgniter
 */

use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Controller;

/**
 * Base contollers, extended into most controllers.
 */
class BaseController extends Controller {

  /**
   * An array of helpers to be loaded automatically.
   *
   * These helpers will be available to all other
   * controllers that extend BaseController.
   *
   * @var array
   */
  protected $helpers = [];

  //phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found

  /**
   * Constructor.
   */
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
    // Do Not Edit This Line.
    parent::initController($request, $response, $logger);

    // --------------------------------------------------------------------
    // Preload any models, libraries, etc, here.
    // --------------------------------------------------------------------
    // E.g.:
    // $this->session = \Config\Services::session();
    date_default_timezone_set('America/New_York');
  }

}
