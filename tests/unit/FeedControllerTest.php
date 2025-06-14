<?php

namespace Tests\Unit;

use App\Controllers\BaseController;
use App\Controllers\Feed;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class FeedControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;

    protected Feed $feedController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->feedController = new Feed();
    }

    public function testControllerExtendsBaseController(): void
    {
        $this->assertInstanceOf(BaseController::class, $this->feedController);
    }

    public function testGetIndexMethodExists(): void
    {
        $this->assertTrue(method_exists($this->feedController, 'getIndex'));
    }

    public function testGetOpmlMethodExists(): void
    {
        $this->assertTrue(method_exists($this->feedController, 'getOpml'));
    }

    public function testGetOpmlSetsCorrectContentType(): void
    {
        $result = $this->controller(Feed::class)
            ->execute('getOpml');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testGetVideofeedMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'getVideofeed'));
    }

    public function testGetVideofeedSetsCorrectContentType(): void
    {
        $result = $this->controller(Feed::class)
            ->execute('getVideofeed');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testGetNewsfeedMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'getNewsfeed'));
    }

    public function testGetNewsfeedSetsCorrectContentType(): void
    {
        $result = $this->controller(Feed::class)
            ->execute('getNewsfeed');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testGetIndexCallsGetNewsfeed(): void
    {
        $result = $this->controller(Feed::class)
            ->execute('getIndex');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }
}
