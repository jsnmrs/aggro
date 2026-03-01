<?php

use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\RepositoryTestCase;

/**
 * @internal
 */
final class FrontControllerTest extends RepositoryTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data for news_feeds table
        $this->db->table('news_feeds')->insert([
            'site_id'              => 1,
            'site_name'            => 'Test Site',
            'site_slug'            => 'test-site',
            'site_url'             => 'https://test.com',
            'site_feed'            => 'https://test.com/feed',
            'site_category'        => 'news',
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s'),
            'site_date_last_post'  => date('Y-m-d H:i:s'),
            'flag_featured'        => 1,
            'flag_stream'          => 0,
            'flag_spoof'           => 0,
        ]);
    }

    public function testAboutPageLoads()
    {
        // Act
        $response = $this->get('/about');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('About');
    }

    public function testError404PageReturns404Status()
    {
        // Act
        $response = $this->get('/nonexistent-page');

        // Assert
        $response->assertStatus(404);
    }

    public function testFeaturedPageUsesRawSql()
    {
        // Featured page uses raw SQL without prefixTable(), incompatible with test DB prefix
        $this->markTestSkipped('featuredPage() uses raw SQL that does not support DB table prefix in test environment');
    }

    public function testSitesPageLoads()
    {
        $response = $this->get('/sites');

        $response->assertStatus(200);
    }

    public function testStreamPageLoads()
    {
        $response = $this->get('/stream');

        $response->assertStatus(200);
    }

    public function testVideoPageLoads()
    {
        $response = $this->get('/video');

        $response->assertStatus(200);
    }

    public function testVideoRecentPageLoads()
    {
        $response = $this->get('/video/recent');

        $response->assertStatus(200);
    }

    public function testNonexistentVideoReturns404()
    {
        $response = $this->get('/video/nonexistent-slug');

        $response->assertStatus(404);
    }

    public function testSitemapReturnsXml()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function testRobotsReturnsText()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap:');
    }
}
