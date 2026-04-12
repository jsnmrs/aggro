<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests for the video view's aspect ratio calculation.
 *
 * @internal
 */
final class VideoViewTest extends CIUnitTestCase
{
    /**
     * Build a minimal $build array for rendering the video view.
     */
    private function makeBuild(array $overrides = []): array
    {
        return array_merge([
            'video_title'           => 'Test Video',
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'video_source_url'      => 'https://example.com/channel',
            'video_source_username' => 'testuser',
            'video_type'            => 'youtube',
            'video_id'              => 'dQw4w9WgXcQ',
            'video_width'           => 800,
            'video_height'          => 450,
        ], $overrides);
    }

    /**
     * Render the video view with the given build data and return the output.
     */
    private function renderVideo(array $build): string
    {
        return view('video', ['build' => $build, 'slug' => 'video']);
    }

    public function testNormalDimensionsProduceCorrectRatio(): void
    {
        $output = $this->renderVideo($this->makeBuild());

        $this->assertStringContainsString('--aspect-ratio: 0.5625', $output);
    }

    public function testZeroWidthFallsBackToDefaultRatio(): void
    {
        $output = $this->renderVideo($this->makeBuild(['video_width' => 0]));

        $this->assertStringContainsString('--aspect-ratio: 0.5625', $output);
    }

    public function testZeroHeightFallsBackToDefaultRatio(): void
    {
        $output = $this->renderVideo($this->makeBuild(['video_height' => 0]));

        $this->assertStringContainsString('--aspect-ratio: 0.5625', $output);
    }

    public function testBothZeroFallsBackToDefaultRatio(): void
    {
        $output = $this->renderVideo($this->makeBuild(['video_width' => 0, 'video_height' => 0]));

        $this->assertStringContainsString('--aspect-ratio: 0.5625', $output);
    }

    public function testStringZeroWidthFallsBackToDefaultRatio(): void
    {
        $output = $this->renderVideo($this->makeBuild(['video_width' => '0']));

        $this->assertStringContainsString('--aspect-ratio: 0.5625', $output);
    }
}
