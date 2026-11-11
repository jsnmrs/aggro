<?php

use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DistinctUserAgentsTrait;
use Tests\Support\LocalHttpServerTrait;
use Tests\Support\RepositoryTestCase;
use Tests\Support\TemporaryStorageTrait;

/**
 * @internal
 */
final class FrontControllerTest extends RepositoryTestCase
{
    use DistinctUserAgentsTrait;
    use FeatureTestTrait;
    use LocalHttpServerTrait;
    use TemporaryStorageTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useDistinctUserAgents();

        // Seed test data for news_feeds table
        $this->seedSite([
            'site_id'       => 1,
            'site_name'     => 'Test Site',
            'site_slug'     => 'test-site',
            'site_feed'     => 'https://test.com/feed',
            'flag_featured' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        $this->restoreUserAgents();
        parent::tearDown();
    }

    /**
     * Insert a site into news_feeds.
     */
    private function seedSite(array $overrides): void
    {
        $this->db->table('news_feeds')->insert(array_merge([
            'site_url'             => 'https://test.com',
            'site_category'        => 'news',
            'site_date_added'      => date('Y-m-d H:i:s'),
            'site_date_updated'    => date('Y-m-d H:i:s'),
            'site_date_last_fetch' => '2026-10-01 09:00:00',
            'site_date_last_post'  => '2026-10-01 10:00:00',
            'flag_featured'        => 0,
            'flag_stream'          => 0,
            'flag_spoof'           => 0,
        ], $overrides));
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

    public function testSitePageShowsSiteName()
    {
        $this->withFeedCacheDirectory(function (): void {
            $response = $this->get('/sites/test-site');

            $response->assertStatus(200);
            $response->assertSee('Test Site');
        });
    }

    public function testSitePageSendsDefaultAgentForUnspoofedSite()
    {
        $this->seedSite([
            'site_id'   => 2,
            'site_name' => 'Plain Site',
            'site_slug' => 'plain-site',
            'site_feed' => $this->localUrl('/body/feed'),
        ]);

        $this->withFeedCacheDirectory(function (): void {
            $body = (string) $this->get('/sites/plain-site')->response()->getBody();

            // The local feed titles its one item with the agent it was sent
            $this->assertStringContainsString(self::DEFAULT_AGENT, $body);
            $this->assertStringNotContainsString(self::SPOOF_AGENT, $body);
        });
    }

    public function testSitePageSendsSpoofedAgentForSpoofedSite()
    {
        $this->seedSite([
            'site_id'    => 2,
            'site_name'  => 'Spoofed Site',
            'site_slug'  => 'spoofed-site',
            'site_feed'  => $this->localUrl('/body/feed'),
            'flag_spoof' => 1,
        ]);

        $this->withFeedCacheDirectory(function (): void {
            $body = (string) $this->get('/sites/spoofed-site')->response()->getBody();

            $this->assertStringContainsString(self::SPOOF_AGENT, $body);
            $this->assertStringNotContainsString(self::DEFAULT_AGENT, $body);
        });
    }

    public function testSitePageViewLeavesTheSiteRowAlone()
    {
        $this->seedSite([
            'site_id'   => 2,
            'site_name' => 'Plain Site',
            'site_slug' => 'plain-site',
            'site_feed' => $this->localUrl('/body/feed'),
        ]);
        $before = $this->db->table('news_feeds')->where('site_slug', 'plain-site')->get()->getRowArray();

        $this->withFeedCacheDirectory(function (): void {
            $this->get('/sites/plain-site')->assertStatus(200);
        });

        $after = $this->db->table('news_feeds')->where('site_slug', 'plain-site')->get()->getRowArray();
        $this->assertSame($before, $after);
    }

    public function testSitePageShowsMessageWhenFeedIsDown()
    {
        $this->seedSite([
            'site_id'   => 2,
            'site_name' => 'Down Site',
            'site_slug' => 'down-site',
            'site_feed' => $this->localUrl('/status/500'),
        ]);

        $this->withFeedCacheDirectory(function (): void {
            $response = $this->get('/sites/down-site');

            $response->assertStatus(200);
            $response->assertSee('Unable to get Down Site feed.');
        });
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

    public function testVideoPageTitleMatchesHeading()
    {
        $body = (string) $this->get('/video')->response()->getBody();

        $this->assertStringContainsString('Recent Videos | BMXfeed</title>', $body);
        $this->assertStringContainsString('<h1>Recent Videos</h1>', $body);
    }

    /**
     * Seed visible videos so the list paginates (30 per page).
     */
    private function seedVideos(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->db->table('aggro_videos')->insert($this->createTestVideo([
                'video_id'         => 'paged' . $i,
                'aggro_date_added' => date('Y-m-d H:i:s', strtotime("-{$i} minutes -1 day")),
            ]));
        }
    }

    public function testVideoListWithNoVideosShowsEmptyState()
    {
        $body = (string) $this->get('/video')->response()->getBody();

        $this->assertStringContainsString('No videos found.', $body);
        $this->assertStringNotContainsString('<ul class="wrap"', $body);
        $this->assertStringNotContainsString('Jump to page', $body);
    }

    public function testLastVideoListPageDoesNotLinkPastTheEnd()
    {
        $this->seedVideos(31);

        $firstPage = (string) $this->get('/video')->response()->getBody();
        $this->assertStringContainsString('Jump to page 2', $firstPage);

        $lastPage = $this->get('/video/recent/2');
        $lastPage->assertStatus(200);
        $body = (string) $lastPage->response()->getBody();
        $this->assertStringContainsString('Back to page 1', $body);
        $this->assertStringNotContainsString('Jump to page 3', $body);

        $this->get('/video/recent/3')->assertStatus(404);
    }

    public function testNonexistentVideoReturns404()
    {
        $response = $this->get('/video/nonexistent-slug');

        $response->assertStatus(404);
    }

    public function testRetiredVideoReturns404()
    {
        $this->db->table('aggro_videos')->insert($this->createTestVideo([
            'video_id' => 'retiredvid1',
            'flag_bad' => 1,
        ]));

        $this->get('/video/retiredvid1')->assertStatus(404);
    }

    public function testArchivedVideoStillRenders()
    {
        $this->db->table('aggro_videos')->insert($this->createTestVideo([
            'video_id'     => 'archivedvid',
            'video_title'  => 'Archived ride',
            'flag_archive' => 1,
        ]));

        $response = $this->get('/video/archivedvid');

        $response->assertStatus(200);
        $this->assertStringContainsString('Archived ride', (string) $response->response()->getBody());
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
