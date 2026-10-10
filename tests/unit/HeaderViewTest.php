<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests for the escaping of the page title in the header include.
 *
 * @internal
 */
final class HeaderViewTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('view');
    }

    /**
     * Render the header include and return the contents of its title element.
     */
    private function renderTitle(array $build, array $data = []): string
    {
        $output = view('includes/header', $data + ['build' => $build, 'slug' => 'video']);

        $this->assertSame(1, preg_match('/<title>(.*?)<\/title>/s', $output, $matches));

        return $matches[1];
    }

    public function testPaginatedVideoListTitleIsTheHeadingOnly(): void
    {
        $title = $this->renderTitle([], ['title' => 'Recent Videos 2 of 8', 'page' => 2, 'endpage' => 8]);

        $this->assertStringEndsWith('Recent Videos 2 of 8 | BMXfeed', $title);
        $this->assertSame(1, substr_count($title, 'Recent Videos'));
    }

    public function testVideoTitleIsEscapedOnce(): void
    {
        $title = $this->renderTitle(['video_title' => 'S&M "Game" <of> Bike']);

        $this->assertStringContainsString('S&amp;M &quot;Game&quot; &lt;of&gt; Bike | ', $title);
    }

    public function testSiteNameIsEscapedOnce(): void
    {
        $title = $this->renderTitle(['site_name' => 'S&M "Bikes" <BMX>']);

        $this->assertStringContainsString('S&amp;M &quot;Bikes&quot; &lt;BMX&gt; | ', $title);
    }

    public function testEmptyVideoTitleFallsBackToSourceName(): void
    {
        $title = $this->renderTitle(['video_title' => '', 'video_source_username' => 'S&M']);

        $this->assertStringContainsString('Untitled video from S&amp;M | ', $title);
        $this->assertStringNotContainsString('> | ', $title);
    }

    public function testPlainVideoTitleIsUnchanged(): void
    {
        $title = $this->renderTitle(['video_title' => 'Test Video']);

        $this->assertStringContainsString('Test Video | ', $title);
    }
}
