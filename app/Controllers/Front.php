<?php

namespace App\Controllers;

use App\Models\AggroModels;
use App\Models\NewsModels;
use App\Services\SitemapService;
use App\Services\ValidationService;
use CodeIgniter\HTTP\ResponseInterface;

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
    public function getIndex(): void
    {
        echo $this->getFeatured();
    }

    /**
     * About page.
     */
    public function getAbout(): string
    {
        $data = [
            'title'     => 'About',
            'slug'      => 'about',
            'canonical' => base_url('about'),
        ];

        return view('about', $data);
    }

    /**
     * Error page.
     */
    public function getError404(): string
    {
        $this->response->setStatusCode(404);

        $data = [
            'title' => 'Page not found',
            'slug'  => '404',
        ];

        return view('error', $data);
    }

    /**
     * Featured page.
     */
    public function getFeatured(): string
    {
        $data = [
            'title'     => 'Featured',
            'slug'      => 'featured',
            'canonical' => base_url('/'),
        ];

        $newsModel     = new NewsModels();
        $data['build'] = $newsModel->featuredPage();

        return view('featured', $data);
    }

    /**
     * Sites page.
     */
    public function getSites(?string $slug = null): string
    {
        helper('aggro');
        $data = [
            'title' => 'Directory',
            'slug'  => 'sites',
        ];

        $newsModel = new NewsModels();

        if ($slug === null) {
            $data['build']     = $newsModel->getSites();
            $data['canonical'] = base_url('sites');

            return view('sites', $data);
        }

        // Use validation service to sanitize slug
        $slug          = $this->validationService->sanitizeSlug($slug);
        $data['build'] = $newsModel->getSite($slug);

        if (! empty($data['build'])) {
            $data['feedfetch'] = fetch_feed($data['build']['site_feed'], 0, 3600);
            $data['canonical'] = base_url('sites/' . $slug);
            $newsModel->updateFeed($slug, $data['feedfetch']);

            return view('site', $data);
        }

        return $this->getError404();
    }

    /**
     * Stream page.
     */
    public function getStream(): string
    {
        $data = [
            'title'     => 'Stream',
            'slug'      => 'stream',
            'canonical' => base_url('stream'),
        ];

        $newsModel = new NewsModels();

        $data['build'] = $newsModel->streamPage();

        return view('stream', $data);
    }

    /**
     * Video pages.
     */
    public function getVideo(?string $slug = null): string
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
     */
    private function handleVideosPagination(): string
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

        // Set canonical URL (page 1 uses /video, subsequent pages include page number)
        $data['canonical'] = $data['page'] === 1
            ? base_url('video')
            : base_url('video/' . $data['sort'] . '/' . $data['page']);

        return view('videos', $data);
    }

    /**
     * Handle individual video display.
     *
     * @param string $slug
     */
    private function handleIndividualVideo($slug): string
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
            $data['canonical'] = base_url('video/' . $slug);

            return view('video', $data);
        }

        return $this->getError404();
    }

    /**
     * XML Sitemap.
     */
    public function sitemap(): ResponseInterface
    {
        $sitemapService = new SitemapService();
        $xml            = $sitemapService->generate();

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setBody($xml);
    }

    /**
     * Robots.txt.
     */
    public function robots(): ResponseInterface
    {
        $content = "User-agent: *\n";
        $content .= "Disallow:\n\n";
        $content .= 'Sitemap: ' . base_url('sitemap.xml') . "\n";

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setBody($content);
    }
}
