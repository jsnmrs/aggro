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
        helper(['view', 'aggro']);
    }

    /**
     * Render the site view with the given feed items.
     *
     * @param list<object> $items Objects with get_permalink(), get_title(), and get_date()
     */
    private function renderSite(array $build, array $items = []): string
    {
        $feedfetch = new class ($items) {
            public bool $error = false;

            public function __construct(private readonly array $items)
            {
            }

            public function get_items(int $start, int $length): array
            {
                return $this->items;
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

    public function testItemWithoutDateRendersWithoutTime(): void
    {
        $item = new class () {
            public function get_permalink(): string
            {
                return 'https://example.com/undated';
            }

            public function get_title(): string
            {
                return 'Undated post';
            }

            public function get_date(string $format): ?string
            {
                return null;
            }
        };

        $output = $this->renderSite([
            'site_name' => 'Example',
            'site_url'  => 'https://example.com/',
            'site_feed' => 'https://example.com/feed',
        ], [$item]);

        $this->assertStringContainsString('<a href="https://example.com/undated" rel="noopener noreferrer">', $output);
        // The link closes the list item: no <time> element follows it
        $this->assertMatchesRegularExpression('#Undated post\s*</a>\s*</li>#', $output);
    }
}
