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
            $data['feedfetch'] = fetch_feed($data['build']['site_feed'], 0, 3600);
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
     * Submit page.
     */
    public function getSubmit()
    {
        $data = [
            'title' => 'Submit',
            'slug'  => 'submit',
        ];

        echo view('submit', $data);
    }

    /**
     * Video pages.
     *
     * @param mixed|null $slug
     */
    public function getVideo($slug = null)
    {
        $data = [
            'title' => 'Videos',
            'slug'  => 'video',
        ];
        $aggroModel = new AggroModels();

        if ($slug === null || $slug === 'recent') {
            $data['page'] = 1;

            if ($this->request->getUri()->getTotalSegments() === 3
              && is_numeric($this->request->getUri()->getSegment(3))) {
                $data['page'] = (int) (esc($this->request->getUri()->getSegment(3)));
            }

            $data['sort']    = 'recent';
            $data['range']   = 'year';
            $data['perpage'] = 30;
            $data['offset']  = ($data['page'] - 1) * $data['perpage'];
            $data['total']   = $aggroModel->getVideosTotal();
            $data['endpage'] = ceil($data['total'] / $data['perpage']);

            if ($data['page'] > $data['endpage'] && $data['endpage'] > 0) {
                return $this->getError404();
            }

            $data['build'] = $aggroModel->getVideos($data['sort'], $data['range'], $data['perpage'], $data['offset']);

            return view('videos', $data);
        }

        $data['build'] = $aggroModel->getVideo(esc($slug));

        if (! empty($data['build'])) {
            return view('video', $data);
        }

        return $this->getError404();
    }
}
