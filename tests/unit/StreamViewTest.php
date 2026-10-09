<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use stdClass;

/**
 * Tests for the stream view's story markup.
 *
 * @internal
 */
final class StreamViewTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('view');
    }

    /**
     * Build a story row as the repository returns it.
     */
    private function makeRow(array $overrides = []): stdClass
    {
        $row = new stdClass();

        foreach (array_merge([
            'story_permalink' => 'https://example.com/story',
            'story_hash'      => 'abc123',
            'story_title'     => 'Test Story',
            'story_date'      => '2024-01-01 12:00:00',
            'site_name'       => 'Example Site',
        ], $overrides) as $key => $value) {
            $row->{$key} = $value;
        }

        return $row;
    }

    /**
     * Render the stream view with the given rows.
     */
    private function renderStream(array $build): string
    {
        return view('stream', ['build' => $build, 'slug' => 'stream']);
    }

    public function testStoryDateIsTimeElementWithOffset(): void
    {
        $output = $this->renderStream([$this->makeRow()]);

        $this->assertSame(1, preg_match('/<span class="ago--muted"><time datetime="(.*?)">(.*?)<\/time> on Example Site<\/span>/s', $output, $matches));
        $this->assertSame('2024-01-01T12:00:00-05:00', $matches[1]);
        $this->assertStringContainsString('ago', $matches[2]);
    }

    public function testEmptyTitleShowsFallback(): void
    {
        $output = $this->renderStream([$this->makeRow(['story_title' => ''])]);

        $this->assertStringContainsString('[missing title]</a>', $output);
    }
}
