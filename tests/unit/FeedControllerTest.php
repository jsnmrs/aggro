<?php

namespace Tests\Unit;

use App\Controllers\BaseController;
use App\Controllers\Feed;
use CodeIgniter\Test\ControllerTestTrait;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class FeedControllerTest extends DatabaseTestCase
{
    use ControllerTestTrait;

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
        if (!$this->db->tableExists('news_feeds')) {
            $this->markTestSkipped('Database table news_feeds not available in test environment');
        }
        
        $result = $this->controller(Feed::class)
            ->execute('getOpml');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testGetVideofeedMethodExists(): void
    {
        $this->assertTrue(method_exists($this->feedController, 'getVideofeed'));
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
        $this->assertTrue(method_exists($this->feedController, 'getNewsfeed'));
    }

    public function testGetNewsfeedSetsCorrectContentType(): void
    {
        if (!$this->db->tableExists('news_feeds')) {
            $this->markTestSkipped('Database table news_feeds not available in test environment');
        }
        
        $result = $this->controller(Feed::class)
            ->execute('getNewsfeed');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testGetIndexCallsGetNewsfeed(): void
    {
        if (!$this->db->tableExists('news_feeds')) {
            $this->markTestSkipped('Database table news_feeds not available in test environment');
        }
        
        $result = $this->controller(Feed::class)
            ->execute('getIndex');

        $response = $result->response();

        $this->assertSame('application/rss+xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }
}
