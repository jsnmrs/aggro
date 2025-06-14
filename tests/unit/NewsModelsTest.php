<?php

use App\Models\NewsModels;
use Tests\Support\ServiceTestCase;

/**
 * @internal
 */
final class NewsModelsTest extends ServiceTestCase
{
    private NewsModels $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new NewsModels();
    }

    public function testFeaturedBuilderReturnsTrueWithNoFeeds()
    {
        // Act - No feeds in database
        $result = $this->model->featuredBuilder();

        // Assert
        $this->assertFalse($result); // Should return false when no featured feeds found
    }

    public function testFeaturedBuilderProcessesFeaturedFeeds()
    {
        // Arrange
        $feedData = [
            'site_id'              => 1,
            'site_name'            => 'Test Site',
            'site_slug'            => 'test-site',
            'site_url'             => 'https://example.com',
            'site_feed'            => 'https://example.com/feed.xml',
            'site_category'        => 'news',
            'flag_featured'        => 1,
            'flag_stream'          => 0,
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $this->db->table('news_feeds')->insert($feedData);

        // Act
        $result = $this->model->featuredBuilder();

        // Assert
        $this->assertTrue($result);
    }

    public function testFeaturedBuilderProcessesStreamFeeds()
    {
        // Arrange
        $feedData = [
            'site_id'              => 1,
            'site_name'            => 'Test Stream Site',
            'site_slug'            => 'test-stream-site',
            'site_url'             => 'https://example.com',
            'site_feed'            => 'https://example.com/feed.xml',
            'site_category'        => 'stream',
            'flag_featured'        => 0,
            'flag_stream'          => 1,
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $this->db->table('news_feeds')->insert($feedData);

        // Act
        $result = $this->model->featuredBuilder();

        // Assert
        $this->assertTrue($result);
    }

    public function testFeaturedBuilderIgnoresNonFeaturedNonStreamFeeds()
    {
        // Arrange
        $feedData = [
            'site_id'              => 1,
            'site_name'            => 'Test Non-Featured Site',
            'site_slug'            => 'test-non-featured',
            'site_url'             => 'https://example.com',
            'site_feed'            => 'https://example.com/feed.xml',
            'site_category'        => 'other',
            'flag_featured'        => 0,
            'flag_stream'          => 0, // Neither featured nor stream
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $this->db->table('news_feeds')->insert($feedData);

        // Act
        $result = $this->model->featuredBuilder();

        // Assert
        $this->assertFalse($result); // Should return false as no featured/stream feeds found
    }

    public function testFeaturedBuilderHandlesMultipleFeeds()
    {
        // Arrange
        $feedData1 = [
            'site_id'              => 1,
            'site_name'            => 'Test Site 1',
            'site_slug'            => 'test-site-1',
            'site_url'             => 'https://example1.com',
            'site_feed'            => 'https://example1.com/feed.xml',
            'site_category'        => 'news',
            'flag_featured'        => 1,
            'flag_stream'          => 0,
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $feedData2 = [
            'site_id'              => 2,
            'site_name'            => 'Test Site 2',
            'site_slug'            => 'test-site-2',
            'site_url'             => 'https://example2.com',
            'site_feed'            => 'https://example2.com/feed.xml',
            'site_category'        => 'stream',
            'flag_featured'        => 0,
            'flag_stream'          => 1,
            'flag_spoof'           => 0,
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'site_date_last_post'  => date('Y-m-d H:i:s', strtotime('-2 hours')),
        ];

        $this->db->table('news_feeds')->insertBatch([$feedData1, $feedData2]);

        // Act
        $result = $this->model->featuredBuilder();

        // Assert
        $this->assertTrue($result);
    }
}
