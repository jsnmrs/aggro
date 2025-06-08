<?php

namespace App\Controllers;

use CodeIgniter\Controller;
// phpcs:disable Drupal.Classes.UnusedUseStatement.UnusedUse
use CodeIgniter\HTTP\CLIRequest;
// phpcs:enable Drupal.Classes.UnusedUseStatement.UnusedUse
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController.
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController.
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Array of helpers.
     *
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line.
        parent::initController($request, $response, $logger);

        // --------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        // --------------------------------------------------------------------
        // E.g.: $this->session = \Config\Services::session();
        date_default_timezone_set('America/New_York');
    }

    /**
     * Validate video slug format.
     *
     * @param string|null $slug
     */
    protected function validateVideoSlug($slug): bool
    {
        if (! is_string($slug) || $slug === '') {
            return false;
        }

        // Check length limits
        if (strlen($slug) > 50) {
            return false;
        }

        // Allow word characters, underscore, and hyphen only
        return (bool) preg_match('/^[\w\-]+$/', $slug);
    }

    /**
     * Validate YouTube video ID format.
     *
     * @param string|null $videoId
     */
    protected function validateYouTubeVideoId($videoId): bool
    {
        if (! is_string($videoId) || $videoId === '') {
            return false;
        }

        // YouTube video IDs are 11 characters, alphanumeric with underscore and hyphen
        return (bool) preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId);
    }

    /**
     * Validate Vimeo video ID format.
     *
     * @param string|null $videoId
     */
    protected function validateVimeoVideoId($videoId): bool
    {
        if (! is_string($videoId) || $videoId === '') {
            return false;
        }

        // Vimeo video IDs are numeric, typically 6-10 digits
        return (bool) preg_match('/^[0-9]{6,10}$/', $videoId);
    }

    /**
     * Validate page number parameter.
     *
     * @param mixed $page
     */
    protected function validatePageNumber($page): bool
    {
        if (! is_numeric($page)) {
            return false;
        }

        $pageNum = (int) $page;

        return $pageNum >= 1 && $pageNum <= 10000; // Reasonable upper limit
    }
}
