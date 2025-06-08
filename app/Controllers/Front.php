<?php

namespace App\Controllers;

use App\Models\AggroModels;
use App\Models\NewsModels;

/**
 * All front-end contollers.
 */
class Front extends BaseController
{
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

        // Sanitize and validate slug
        $slug = trim($slug ?? '');
        $slug = preg_replace('/[^\w\-]/', '', $slug); // Allow word chars, underscore, and hyphen

        $data = [
            'title' => 'Videos',
            'slug'  => 'video',
        ];
        $aggroModel = new AggroModels();

        if ($slug === '' || $slug === 'recent') {
            $data['page'] = 1;

            // Validate page number parameter
            if ($this->request->getUri()->getTotalSegments() === 3) {
                $pageParam = $this->request->getUri()->getSegment(3);
                if (! $this->validatePageNumber($pageParam)) {
                    return $this->getError404();
                }
                $data['page'] = (int) $pageParam;
            }

            $data['sort']    = 'recent';
            $data['range']   = 'year';
            $data['perpage'] = 30;
            $data['offset']  = ($data['page'] - 1) * $data['perpage'];
            $data['total']   = $aggroModel->getVideosTotal();
            $data['endpage'] = ceil((int) $data['total'] / $data['perpage']);

            if ($data['page'] > $data['endpage'] && $data['endpage'] > 0) {
                return $this->getError404();
            }

            $data['build'] = $aggroModel->getVideos($data['range'], (string) $data['perpage'], (string) $data['offset']);

            return view('videos', $data);
        }

        // Validate individual video slug
        if (! $this->validateVideoSlug($slug)) {
            return $this->getError404();
        }

        $data['build'] = $aggroModel->getVideo($slug);

        if (! empty($data['build'])) {
            return view('video', $data);
        }

        return $this->getError404();
    }
}
