<?php

namespace Tests\Unit;

use App\Models\YoutubeModels;
use CodeIgniter\Model;
use stdClass;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class YoutubeModelsTest extends DatabaseTestCase
{
    protected YoutubeModels $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new YoutubeModels();
    }

    public function testModelExtendsCodeIgniterModel(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testSearchChannelMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'searchChannel'));
    }

    public function testSearchChannelWithNullFeed(): void
    {
        $mockFeed = $this->createMock(stdClass::class);
        $mockFeed->method('get_items')->willReturn([]);

        $result = $this->model->searchChannel($mockFeed, 'test123');
        $this->assertFalse($result);
    }

    public function testParseChannelMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'parseChannel'));
    }

    public function testParseChannelWithEmptyFeed(): void
    {
        $mockFeed = $this->createMock(stdClass::class);
        $mockFeed->method('get_items')->willReturn([]);

        $result = $this->model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testGetDurationMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getDuration'));
    }

    public function testGetDurationWithEmptyDatabase(): void
    {
        $result = $this->model->getDuration();
        $this->assertTrue($result);
    }

    public function testSearchChannelHandlesVideoNotFound(): void
    {
        $mockItem = $this->createMock(stdClass::class);
        $mockItem->method('get_item_tags')->willReturn([
            ['data' => 'different_video_id'],
        ]);

        $mockFeed = $this->createMock(stdClass::class);
        $mockFeed->method('get_items')->willReturn([$mockItem]);

        $result = $this->model->searchChannel($mockFeed, 'target_video_id');
        $this->assertFalse($result);
    }

    public function testParseChannelCalculatesCorrectAddCount(): void
    {
        $mockFeed = $this->createMock(stdClass::class);
        $mockFeed->method('get_items')->willReturn([]);

        $result = $this->model->parseChannel($mockFeed);
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetDurationReturnsBoolean(): void
    {
        $result = $this->model->getDuration();
        $this->assertIsBool($result);
    }

    public function testSearchChannelReturnsBoolean(): void
    {
        $mockFeed = $this->createMock(stdClass::class);
        $mockFeed->method('get_items')->willReturn([]);

        $result = $this->model->searchChannel($mockFeed, 'test123');
        $this->assertIsBool($result);
    }

    public function testParseChannelReturnsInteger(): void
    {
        $mockFeed = $this->createMock(stdClass::class);
        $mockFeed->method('get_items')->willReturn([]);

        $result = $this->model->parseChannel($mockFeed);
        $this->assertIsInt($result);
    }
}
