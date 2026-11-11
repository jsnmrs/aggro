<?php

namespace Tests\Unit;

use App\Controllers\Feed;
use CodeIgniter\Test\ControllerTestTrait;
use Tests\Support\ServiceTestCase;

/**
 * @internal
 */
final class FeedControllerTest extends ServiceTestCase
{
    use ControllerTestTrait;

    protected Feed $feedController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->feedController = new Feed();
    }

    public function testGetOpmlSetsCorrectContentType(): void
    {
        $result = $this->controller(Feed::class)
            ->execute('getOpml');

        $response = $result->response();

        $this->assertSame('text/x-opml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testGetVideofeedSetsCorrectContentType(): void
    {
        $result = $this->controller(Feed::class)
            ->execute('getVideofeed');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testGetNewsfeedSetsCorrectContentType(): void
    {
        $result = $this->controller(Feed::class)
            ->execute('getNewsfeed');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }
}
