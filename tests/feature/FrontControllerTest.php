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
            'site_id' => 1,
            'site_name' => 'Test Site',
            'site_slug' => 'test-site',
            'site_url' => 'https://test.com',
            'site_feed' => 'https://test.com/feed',
            'site_category' => 'news',
            'site_date_added' => date('Y-m-d H:i:s'),
            'site_date_updated' => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s'),
            'site_date_last_post' => date('Y-m-d H:i:s'),
            'flag_featured' => 1,
            'flag_stream' => 0,
            'flag_spoof' => 0,
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

}