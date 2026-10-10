<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests for the featured view's site headings and article labels.
 *
 * @internal
 */
final class FeaturedViewTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('view');
    }

    /**
     * Build a featured site row as the controller passes it.
     */
    private function makeRow(string $slug, string $name): array
    {
        return [
            'site_slug'           => $slug,
            'site_name'           => $name,
            'site_date_last_post' => '2024-01-01 12:00:00',
            'story1'              => [
                'story_title'     => 'First Story',
                'story_permalink' => 'https://example.com/first',
                'story_hash'      => 'hash1',
            ],
        ];
    }

    /**
     * Render the featured view with the given site rows.
     */
    private function renderFeatured(array $build): string
    {
        return view('featured', ['build' => $build, 'slug' => 'featured']);
    }

    public function testHeadingHoldsOnlyTheSiteLink(): void
    {
        $output = $this->renderFeatured([$this->makeRow('fatbmx', 'FATBMX')]);

        $this->assertStringContainsString('<h2 id="site-fatbmx">', $output);
        $this->assertSame(1, preg_match('/<h2[^>]*>(.*?)<\/h2>/s', $output, $matches));
        $this->assertStringContainsString('<a href="/sites/fatbmx">FATBMX <span class="visually-hidden">on BMXfeed</span></a>', $matches[1]);
        $this->assertStringNotContainsString('<time', $matches[1]);
    }

    public function testLastPostTimeFollowsTheHeading(): void
    {
        $output = $this->renderFeatured([$this->makeRow('fatbmx', 'FATBMX')]);

        $this->assertStringContainsString('</h2>', $output);
        $this->assertStringContainsString('<p class="hug">Last post <time class="ago--muted" datetime="2024-01-01T12:00:00-05:00">', $output);
    }

    public function testArticleIsLabelledByItsHeading(): void
    {
        $output = $this->renderFeatured([
            $this->makeRow('fatbmx', 'FATBMX'),
            $this->makeRow('bloom', 'Bloom'),
        ]);

        $this->assertStringContainsString('<article class="box box--feature" aria-labelledby="site-fatbmx">', $output);
        $this->assertStringContainsString('<article class="box box--feature" aria-labelledby="site-bloom">', $output);
        $this->assertStringContainsString('<h2 id="site-bloom">', $output);
    }

    public function testSlugIsEscapedInIdAndHref(): void
    {
        $output = $this->renderFeatured([$this->makeRow('s&m"bikes', 'S&M')]);

        $this->assertStringContainsString('aria-labelledby="site-s&amp;m&quot;bikes"', $output);
        $this->assertStringContainsString('<h2 id="site-s&amp;m&quot;bikes">', $output);
        $this->assertStringNotContainsString('id="site-s&m"', $output);
    }
}
