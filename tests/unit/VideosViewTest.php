<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use stdClass;

/**
 * Tests for the videos list view's grid markup.
 *
 * @internal
 */
final class VideosViewTest extends CIUnitTestCase
{
    /**
     * Build a video row as the repository returns it.
     */
    private function makeRow(string $id, string $title, string $source = 'Test Channel'): stdClass
    {
        $row                        = new stdClass();
        $row->video_id              = $id;
        $row->video_title           = $title;
        $row->video_source_username = $source;

        return $row;
    }

    /**
     * Render the videos view with the given rows and page counts.
     */
    private function renderVideos(array $build, int $endpage, int $page = 1, string $title = 'Recent Videos'): string
    {
        return view('videos', [
            'build'   => $build,
            'title'   => $title,
            'page'    => $page,
            'endpage' => $endpage,
            'sort'    => 'recent',
            'slug'    => 'video',
        ]);
    }

    public function testHeadingIsThePageTitle(): void
    {
        $output = $this->renderVideos([$this->makeRow('abc123', 'First Video')], 8, 2, 'Recent Videos 2 of 8');

        $this->assertStringContainsString('<h1>Recent Videos 2 of 8</h1>', $output);
        $this->assertStringContainsString('Recent Videos 2 of 8 | BMXfeed</title>', $output);
    }

    public function testGridIsAListOfVideos(): void
    {
        $output = $this->renderVideos([
            $this->makeRow('abc123', 'First Video'),
            $this->makeRow('def456', 'Second Video'),
        ], 1);

        $this->assertStringContainsString('<ul class="wrap" role="list">', $output);
        $this->assertSame(2, substr_count($output, '<li class="box box--video">'));
        $this->assertStringNotContainsString('<div class="box box--video">', $output);
    }

    public function testEmptyTitleLinkStillHasAName(): void
    {
        $output = $this->renderVideos([$this->makeRow('abc123', '', 'S&M Bikes')], 1);

        $this->assertStringContainsString('<p>Untitled video from S&amp;M Bikes</p>', $output);
        $this->assertStringNotContainsString('<p></p>', $output);
    }

    public function testEmptyStateIsNotAList(): void
    {
        $output = $this->renderVideos([], 0);

        $this->assertStringContainsString('No videos found.', $output);
        $this->assertStringNotContainsString('<ul class="wrap"', $output);
    }
}
