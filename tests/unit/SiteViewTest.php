<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests for the single site view's link markup.
 *
 * @internal
 */
final class SiteViewTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('view');
    }

    /**
     * Render the site view with an empty feed.
     */
    private function renderSite(array $build): string
    {
        $feedfetch = new class () {
            public bool $error = false;

            public function get_items(int $start, int $length): array
            {
                return [];
            }
        };

        return view('site', ['build' => $build, 'feedfetch' => $feedfetch, 'slug' => 'sites']);
    }

    public function testSiteAndFeedLinksAreLabelledForScreenReaders(): void
    {
        $output = $this->renderSite([
            'site_name' => 'S&M',
            'site_url'  => 'https://example.com/',
            'site_feed' => 'https://example.com/feed?format=rss',
        ]);

        $this->assertStringContainsString(
            '<a class="url" href="https://example.com/"><span class="visually-hidden">S&amp;M website: </span>https://example.com/</a>',
            $output,
        );
        $this->assertStringContainsString(
            '<a class="url" href="https://example.com/feed?format=rss"><span class="visually-hidden">S&amp;M RSS feed: </span>https://example.com/feed?format=rss</a>',
            $output,
        );
    }
}
