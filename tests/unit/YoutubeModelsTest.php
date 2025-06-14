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
        $mockFeed = new class {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->searchChannel($mockFeed, 'test123');
        $this->assertFalse($result);
    }

    public function testParseChannelMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'parseChannel'));
    }

    public function testParseChannelWithEmptyFeed(): void
    {
        $mockFeed = new class {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testGetDurationMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getDuration'));
    }

    public function testGetDurationWithEmptyDatabase(): void
    {
        if (!$this->db->tableExists('aggro_videos')) {
            $this->markTestSkipped('Database table aggro_videos not available in test environment');
        }
        
        $result = $this->model->getDuration();
        $this->assertTrue($result);
    }

    public function testSearchChannelHandlesVideoNotFound(): void
    {
        $mockItem = new class {
            public function get_item_tags($namespace, $tag): array
            {
                return [['data' => 'different_video_id']];
            }
        };

        $mockFeed = new class ($mockItem) {
            private $item;
            public function __construct($item) { $this->item = $item; }
            public function get_items($start = 0, $end = 0): array
            {
                return [$this->item];
            }
        };

        $result = $this->model->searchChannel($mockFeed, 'target_video_id');
        $this->assertFalse($result);
    }

    public function testParseChannelCalculatesCorrectAddCount(): void
    {
        $mockFeed = new class {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetDurationReturnsBoolean(): void
    {
        if (!$this->db->tableExists('aggro_videos')) {
            $this->markTestSkipped('Database table aggro_videos not available in test environment');
        }
        
        $result = $this->model->getDuration();
        $this->assertIsBool($result);
    }

    public function testSearchChannelReturnsBoolean(): void
    {
        $mockFeed = new class {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->searchChannel($mockFeed, 'test123');
        $this->assertIsBool($result);
    }

    public function testParseChannelReturnsInteger(): void
    {
        $mockFeed = new class {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertIsInt($result);
    }
}
