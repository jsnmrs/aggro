<?php

namespace App\Controllers;

use App\Models\AggroModels;
use App\Models\NewsModels;
use App\Services\ValidationService;

/**
 * All front-end controllers.
 */
class Front extends BaseController
{
    protected $validationService;
    
    public function __construct()
    {
        $this->validationService = new ValidationService();
    }
    /**
     * Home -> featured page.
     */
    public function getIndex()
    {
        $this->getFeatured();
    }

    /**
     * About page.
     */
    public function getAbout()
    {
        $data = [
            'title' => 'About',
            'slug'  => 'about',
        ];

        return view('about', $data);
    }

    /**
     * Error page.
     */
    public function getError404()
    {
        $this->response->setStatusCode(404);

        $data = [
            'title' => 'Page not found',
            'slug'  => '404',
        ];

        echo view('error', $data);
    }

    /**
     * Featured page.
     */
    public function getFeatured()
    {
        $data = [
            'title' => 'Featured',
            'slug'  => 'featured',
        ];

        $newsModel     = new NewsModels();
        $data['build'] = $newsModel->featuredPage();

        echo view('featured', $data);
    }

    /**
     * Sites page.
     *
     * @param mixed|null $slug
     */
    public function getSites($slug = null)
    {
        helper('aggro');
        $data = [
            'title' => 'Directory',
            'slug'  => 'sites',
        ];

        $newsModel = new NewsModels();

        if ($slug === null) {
            $data['build'] = $newsModel->getSites();

            return view('sites', $data);
        }

        // Use validation service to sanitize slug
        $slug = $this->validationService->sanitizeSlug($slug);
        $data['build'] = $newsModel->getSite($slug);

        if (! empty($data['build'])) {
            $data['feedfetch'] = fetch_feed($data['build']['site_feed'], '0', '3600');
            $newsModel->updateFeed($slug, $data['feedfetch']);

            return view('site', $data);
        }

        return $this->getError404();
    }

    /**
     * Stream page.
     */
    public function getStream()
    {
        $data = [
            'title' => 'Stream',
            'slug'  => 'stream',
        ];

        $newsModel = new NewsModels();

        $data['build'] = $newsModel->streamPage();
        echo view('stream', $data);
    }

    /**
     * Video pages.
     *
     * @param mixed|null $slug
     */
    public function getVideo($slug = null)
    {
        helper('html');
        $slug = $this->sanitizeSlug($slug);

        if ($this->isVideoListRequest($slug)) {
            return $this->handleVideosPagination();
        }

        return $this->handleIndividualVideo($slug);
    }

    /**
     * Sanitize video slug input.
     *
     * @param string|null $slug
     */
    private function sanitizeSlug($slug): string
    {
        return $this->validationService->sanitizeSlug($slug ?? '');
    }

    /**
     * Check if request is for video list (not individual video).
     *
     * @param string $slug
     */
    private function isVideoListRequest($slug): bool
    {
        return $slug === '' || $slug === 'recent';
    }

    /**
     * Handle paginated video list display.
     *
     * @return mixed
     */
    private function handleVideosPagination()
    {
        $data = [
            'title' => 'Videos',
            'slug'  => 'video',
            'page'  => 1,
        ];

        // Validate and set page number
        if ($this->request->getUri()->getTotalSegments() === 3) {
            $pageParam = $this->request->getUri()->getSegment(3);
            if (! $this->validatePageNumber($pageParam)) {
                return $this->getError404();
            }
            $data['page'] = (int) $pageParam;
        }

        // Set pagination parameters
        $data['sort']    = 'recent';
        $data['range']   = 'year';
        $data['perpage'] = 30;
        $data['offset']  = ($data['page'] - 1) * $data['perpage'];

        // Get total and calculate pagination
        $aggroModel      = new AggroModels();
        $data['total']   = $aggroModel->getVideosTotal();
        $data['endpage'] = ceil((int) $data['total'] / $data['perpage']);

        // Validate page exists
        if ($data['page'] > $data['endpage'] && $data['endpage'] > 0) {
            return $this->getError404();
        }

        // Get videos and render
        $data['build'] = $aggroModel->getVideos($data['range'], (string) $data['perpage'], (string) $data['offset']);

        return view('videos', $data);
    }

    /**
     * Handle individual video display.
     *
     * @param string $slug
     *
     * @return mixed
     */
    private function handleIndividualVideo($slug)
    {
        // Validate video slug format
        if (! $this->validateVideoSlug($slug)) {
            return $this->getError404();
        }

        $data = [
            'title' => 'Videos',
            'slug'  => 'video',
        ];

        // Get video data
        $aggroModel    = new AggroModels();
        $data['build'] = $aggroModel->getVideo($slug);

        if (! empty($data['build'])) {
            return view('video', $data);
        }

        return $this->getError404();
    }
}
