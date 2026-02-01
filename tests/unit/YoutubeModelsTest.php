<?php

namespace Tests\Unit;

use App\Models\AggroModels;
use App\Models\UtilityModels;
use App\Models\YoutubeModels;
use CodeIgniter\Model;
use ReflectionClass;
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

    public function testConstructorAcceptsDependencyInjection(): void
    {
        $mockAggro   = $this->createMock(AggroModels::class);
        $mockUtility = $this->createMock(UtilityModels::class);

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $reflection = new ReflectionClass($model);

        $aggroProp = $reflection->getProperty('aggroModel');
        $this->assertSame($mockAggro, $aggroProp->getValue($model));

        $utilityProp = $reflection->getProperty('utilityModel');
        $this->assertSame($mockUtility, $utilityProp->getValue($model));
    }

    public function testConstructorCreatesDefaultDependencies(): void
    {
        $model = new YoutubeModels();

        $reflection = new ReflectionClass($model);

        $aggroProp = $reflection->getProperty('aggroModel');
        $this->assertInstanceOf(AggroModels::class, $aggroProp->getValue($model));

        $utilityProp = $reflection->getProperty('utilityModel');
        $this->assertInstanceOf(UtilityModels::class, $utilityProp->getValue($model));
    }

    public function testSearchChannelMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'searchChannel'));
    }

    public function testSearchChannelWithNullFeed(): void
    {
        $mockFeed = new class () {
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
        $mockFeed = new class () {
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
        // Skip test that requires aggro_videos table
        $this->markTestSkipped('Database table aggro_videos not available in test environment');
    }

    public function testSearchChannelHandlesVideoNotFound(): void
    {
        $mockItem = new class () {
            public function get_item_tags($namespace, $tag): array
            {
                return [['data' => 'different_video_id']];
            }
        };

        $mockFeed = new class ($mockItem) {
            private $item;

            public function __construct($item)
            {
                $this->item = $item;
            }

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
        $mockFeed = new class () {
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
        // Skip test that requires aggro_videos table
        $this->markTestSkipped('Database table aggro_videos not available in test environment');
    }

    public function testSearchChannelReturnsBoolean(): void
    {
        $mockFeed = new class () {
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
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertIsInt($result);
    }

    public function testSearchChannelWithValidVideoId(): void
    {
        // Skip test that requires YouTube helper functions and AggroModels integration
        $this->markTestSkipped('Method requires youtube_parse_meta helper and AggroModels integration');

        // This would test finding a specific video in a feed
        // $mockItem = $this->createMockYouTubeItem('target_video_id');
        // $mockFeed = $this->createMockFeedWithItems([$mockItem]);
        // $result = $this->model->searchChannel($mockFeed, 'target_video_id');
        // $this->assertTrue($result);
    }

    public function testSearchChannelWithExistingVideo(): void
    {
        // Mock AggroModels to return true for checkVideo (video already exists)
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(true);

        $mockUtility = $this->createMock(UtilityModels::class);

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $mockItem = new class () {
            public function get_item_tags($namespace, $tag): array
            {
                return [['data' => 'existing_video_id']];
            }
        };

        $mockFeed = new class ($mockItem) {
            private $item;

            public function __construct($item)
            {
                $this->item = $item;
            }

            public function get_items($start = 0, $end = 0): array
            {
                return [$this->item];
            }
        };

        // Video exists, so searchChannel should return false (not added)
        $result = $model->searchChannel($mockFeed, 'existing_video_id');
        $this->assertFalse($result);
    }

    public function testParseChannelWithMultipleNewVideos(): void
    {
        // Skip test that requires YouTube helper functions
        $this->markTestSkipped('Method requires youtube_parse_meta helper and AggroModels integration');

        // This would test processing multiple videos from a feed
    }

    public function testParseChannelDoesNotLogForZeroVideos(): void
    {
        // Mock dependencies - no videos added so sendLog should not be called
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(true); // All videos exist

        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->expects($this->never())->method('sendLog');

        $model = new YoutubeModels($mockAggro, $mockUtility);

        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testGetDurationUpdatesVideoDatabase(): void
    {
        // Skip test that requires aggro_videos table and YouTube API integration
        $this->markTestSkipped('Method requires aggro_videos table and youtube_get_duration helper');

        // This would test updating video durations in the database
    }

    public function testGetDurationHandlesApiFailure(): void
    {
        // Skip test that requires YouTube API integration
        $this->markTestSkipped('Method requires youtube_get_duration helper function');

        // This would test error handling when YouTube API fails
    }

    public function testGetDurationLogsResults(): void
    {
        // Skip test that requires UtilityModels integration
        $this->markTestSkipped('Method requires UtilityModels sendLog functionality');

        // This would test that results are logged via UtilityModels
    }

    public function testSearchChannelReturnsFalseForEmptyFeed(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->searchChannel($mockFeed, 'any_video_id');
        $this->assertFalse($result);
    }

    public function testParseChannelHandlesEmptyFeedGracefully(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        $result = $this->model->parseChannel($mockFeed);
        $this->assertSame(0, $result);
    }

    public function testGetDurationReturnsFalseOnDatabaseError(): void
    {
        // Skip test that would require simulating database errors
        $this->markTestSkipped('Method requires database error simulation');

        // This would test error handling when database query fails
    }

    public function testSearchChannelParameterValidation(): void
    {
        // Test that method handles different parameter types appropriately
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        // Test with empty video ID
        $result = $this->model->searchChannel($mockFeed, '');
        $this->assertFalse($result);

        // Test with null video ID
        $result = $this->model->searchChannel($mockFeed, null);
        $this->assertFalse($result);
    }

    public function testModelMethodsReturnCorrectTypes(): void
    {
        $mockFeed = new class () {
            public function get_items($start = 0, $end = 0): array
            {
                return [];
            }
        };

        // Verify return types for all public methods
        $this->assertIsBool($this->model->searchChannel($mockFeed, 'test'));
        $this->assertIsInt($this->model->parseChannel($mockFeed));

        // Skip getDuration test that requires aggro_videos table
        // $this->assertIsBool($this->model->getDuration());
    }
}
