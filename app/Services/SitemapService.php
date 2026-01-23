<?php

namespace App\Services;

use App\Models\NewsModels;
use DateTime;

class SitemapService
{
    /**
     * Generate the XML sitemap.
     */
    public function generate(): string
    {
        $urls = $this->getStaticUrls();
        $urls = array_merge($urls, $this->getDynamicSiteUrls());

        return $this->buildXml($urls);
    }

    /**
     * Get static page URLs.
     *
     * @return array Array of URL entries
     */
    private function getStaticUrls(): array
    {
        return [
            [
                'loc'        => base_url('/'),
                'changefreq' => 'weekly',
                'priority'   => '1.0',
            ],
            [
                'loc'        => base_url('about'),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ],
            [
                'loc'        => base_url('sites'),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ],
            [
                'loc'        => base_url('video'),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ],
        ];
    }

    /**
     * Get dynamic site URLs from the database.
     *
     * @return array Array of URL entries
     */
    private function getDynamicSiteUrls(): array
    {
        $newsModel = new NewsModels();
        $sites     = $newsModel->getSites();
        $urls      = [];

        foreach ($sites as $site) {
            $entry = [
                'loc'        => base_url('sites/' . $site->site_slug),
                'changefreq' => 'daily',
                'priority'   => '0.6',
            ];

            // Add lastmod if site_date_updated is available
            if (! empty($site->site_date_updated)) {
                $entry['lastmod'] = $this->formatW3CDatetime($site->site_date_updated);
            }

            $urls[] = $entry;
        }

        return $urls;
    }

    /**
     * Format a date string as W3C datetime.
     *
     * @param string $datetime MySQL datetime string
     */
    private function formatW3CDatetime(string $datetime): string
    {
        $date = new DateTime($datetime);

        return $date->format('Y-m-d\TH:i:sP');
    }

    /**
     * Build the XML sitemap string.
     *
     * @param array $urls Array of URL entries
     */
    private function buildXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";

            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            }

            if (isset($url['changefreq'])) {
                $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            }

            if (isset($url['priority'])) {
                $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        return $xml;
    }
}
