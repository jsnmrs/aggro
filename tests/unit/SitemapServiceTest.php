<?php

use App\Services\SitemapService;
use Tests\Support\ServiceTestCase;

/**
 * @internal
 */
final class SitemapServiceTest extends ServiceTestCase
{
    private SitemapService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SitemapService();
    }

    public function testGenerateReturnsValidXml()
    {
        $xml = $this->service->generate();

        // Verify it's valid XML
        $this->assertIsString($xml);
        $doc = simplexml_load_string($xml);
        $this->assertNotFalse($doc, 'Generated sitemap must be valid XML');
    }

    public function testGenerateIncludesXmlDeclaration()
    {
        $xml = $this->service->generate();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
    }

    public function testGenerateIncludesSitemapNamespace()
    {
        $xml = $this->service->generate();
        $doc = simplexml_load_string($xml);

        $this->assertSame('http://www.sitemaps.org/schemas/sitemap/0.9', (string) $doc->getNamespaces()['']);
    }

    public function testGenerateIncludesHomepage()
    {
        $xml = $this->service->generate();

        $this->assertStringContainsString('<loc>' . base_url('/') . '</loc>', $xml);
    }

    public function testGenerateIncludesAboutPage()
    {
        $xml = $this->service->generate();

        $this->assertStringContainsString('<loc>' . base_url('about') . '</loc>', $xml);
    }

    public function testGenerateIncludesSitesPage()
    {
        $xml = $this->service->generate();

        $this->assertStringContainsString('<loc>' . base_url('sites') . '</loc>', $xml);
    }

    public function testGenerateIncludesVideoPage()
    {
        $xml = $this->service->generate();

        $this->assertStringContainsString('<loc>' . base_url('video') . '</loc>', $xml);
    }

    public function testGenerateIncludesStaticPagesWithWeeklyChangefreq()
    {
        $xml = $this->service->generate();

        // Check that static pages have weekly changefreq
        $doc = simplexml_load_string($xml);
        $doc->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // Find the homepage URL entry
        $homepageUrls = $doc->xpath("//s:url[s:loc='" . base_url('/') . "']");
        $this->assertNotEmpty($homepageUrls, 'Homepage should be in sitemap');
        $this->assertSame('weekly', (string) $homepageUrls[0]->changefreq);
    }

    public function testGenerateIncludesStaticPagesWithHighPriority()
    {
        $xml = $this->service->generate();

        $doc = simplexml_load_string($xml);
        $doc->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $homepageUrls = $doc->xpath("//s:url[s:loc='" . base_url('/') . "']");
        $this->assertNotEmpty($homepageUrls);
        $this->assertSame('1.0', (string) $homepageUrls[0]->priority);
    }

    public function testGenerateIncludesDynamicSiteUrls()
    {
        // Insert test site
        $this->insertTestSite($this->createTestSiteData([
            'site_id'   => 1,
            'site_slug' => 'test-site',
        ]));

        $xml = $this->service->generate();

        $this->assertStringContainsString('<loc>' . base_url('sites/test-site') . '</loc>', $xml);
    }

    public function testGenerateIncludesDynamicSitesWithDailyChangefreq()
    {
        $this->insertTestSite($this->createTestSiteData([
            'site_id'   => 1,
            'site_slug' => 'test-site',
        ]));

        $xml = $this->service->generate();
        $doc = simplexml_load_string($xml);
        $doc->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $siteUrls = $doc->xpath("//s:url[s:loc='" . base_url('sites/test-site') . "']");
        $this->assertNotEmpty($siteUrls, 'Site URL should be in sitemap');
        $this->assertSame('daily', (string) $siteUrls[0]->changefreq);
    }

    public function testGenerateUsesW3CDatetimeForLastmod()
    {
        $this->insertTestSite($this->createTestSiteData([
            'site_id'           => 1,
            'site_slug'         => 'test-site',
            'site_date_updated' => '2025-01-15 12:30:00',
        ]));

        $xml = $this->service->generate();
        $doc = simplexml_load_string($xml);
        $doc->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $siteUrls = $doc->xpath("//s:url[s:loc='" . base_url('sites/test-site') . "']");
        $this->assertNotEmpty($siteUrls);

        $lastmod = (string) $siteUrls[0]->lastmod;
        // W3C datetime format: YYYY-MM-DDTHH:MM:SS+HH:MM or YYYY-MM-DDTHH:MM:SSZ
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $lastmod,
            'lastmod should be in W3C datetime format',
        );
    }

    public function testGenerateIncludesMultipleSites()
    {
        $this->insertTestSite($this->createTestSiteData([
            'site_id'   => 1,
            'site_name' => 'Site One',
            'site_slug' => 'site-one',
        ]));
        $this->insertTestSite($this->createTestSiteData([
            'site_id'   => 2,
            'site_name' => 'Site Two',
            'site_slug' => 'site-two',
        ]));

        $xml = $this->service->generate();

        $this->assertStringContainsString('<loc>' . base_url('sites/site-one') . '</loc>', $xml);
        $this->assertStringContainsString('<loc>' . base_url('sites/site-two') . '</loc>', $xml);
    }

    public function testGenerateWorksWithNoSites()
    {
        // No sites inserted, should still return valid XML with static pages
        $xml = $this->service->generate();

        $this->assertIsString($xml);
        $doc = simplexml_load_string($xml);
        $this->assertNotFalse($doc);

        // Should still have static pages
        $this->assertStringContainsString('<loc>' . base_url('/') . '</loc>', $xml);
    }

    /**
     * Helper method to insert test site data.
     */
    protected function insertTestSite(array $data): bool
    {
        return $this->db->table('news_feeds')->insert($data);
    }

    /**
     * Helper method to create test site data with all required fields.
     *
     * @param array $overrides Optional data to override defaults
     */
    protected function createTestSiteData(array $overrides = []): array
    {
        $defaults = [
            'site_id'              => 1,
            'site_name'            => 'Test Site',
            'site_slug'            => 'test-site',
            'site_url'             => 'https://example.com',
            'site_feed'            => 'https://example.com/feed',
            'site_category'        => 'test',
            'site_date_added'      => '2025-01-01 00:00:00',
            'site_date_updated'    => '2025-01-15 12:30:00',
            'site_date_last_fetch' => '2025-01-15 12:00:00',
            'site_date_last_post'  => '2025-01-15 10:00:00',
            'flag_featured'        => 0,
            'flag_stream'          => 0,
            'flag_spoof'           => 0,
        ];

        return array_merge($defaults, $overrides);
    }
}
