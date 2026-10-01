<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests for the escaping of the source URL in the video RSS feed.
 *
 * @internal
 */
final class RssViewTest extends CIUnitTestCase
{
    /**
     * Build a minimal video row for rendering the video RSS feed.
     */
    private function makeRow(array $overrides = []): object
    {
        return (object) array_merge([
            'video_title'           => 'Test Video',
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'video_date_uploaded'   => date('Y-m-d H:i:s'),
            'video_source_url'      => 'https://example.com/channel',
            'video_source_username' => 'testuser',
            'video_type'            => 'youtube',
            'video_id'              => 'dQw4w9WgXcQ',
            'video_width'           => 800,
            'video_height'          => 450,
        ], $overrides);
    }

    /**
     * Render the video RSS feed with the given rows and return the output.
     */
    private function renderFeed(array $rows): string
    {
        return view('xml/rss', ['build' => $rows]);
    }

    public function testSourceUrlIsEscaped(): void
    {
        $output = $this->renderFeed([$this->makeRow(['video_source_url' => 'https://example.com/channel?a=1&b="x"'])]);

        $this->assertStringContainsString('Uploaded by <a href="https://example.com/channel?a=1&amp;b=&quot;x&quot;">testuser</a>', $output);
        $this->assertStringNotContainsString('b="x"', $output);
    }

    public function testPlainSourceUrlIsUnchanged(): void
    {
        $output = $this->renderFeed([$this->makeRow()]);

        $this->assertStringContainsString('Uploaded by <a href="https://example.com/channel">testuser</a>', $output);
    }
}
