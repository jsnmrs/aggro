<?php

namespace Tests\Unit;

use App\Models\AggroModels;
use App\Models\UtilityModels;
use App\Models\VimeoModels;
use CodeIgniter\Model;
use ReflectionClass;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class VimeoModelsTest extends DatabaseTestCase
{
    protected VimeoModels $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new VimeoModels();
    }

    public function testModelExtendsCodeIgniterModel(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testConstructorAcceptsDependencyInjection(): void
    {
        $mockAggro   = $this->createMock(AggroModels::class);
        $mockUtility = $this->createMock(UtilityModels::class);

        $model = new VimeoModels($mockAggro, $mockUtility);

        $reflection = new ReflectionClass($model);

        $aggroProp = $reflection->getProperty('aggroModel');
        $this->assertSame($mockAggro, $aggroProp->getValue($model));

        $utilityProp = $reflection->getProperty('utilityModel');
        $this->assertSame($mockUtility, $utilityProp->getValue($model));
    }

    public function testConstructorCreatesDefaultDependencies(): void
    {
        $model = new VimeoModels();

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

    public function testSearchChannelWithFalseFeed(): void
    {
        $result = $this->model->searchChannel(false, 'test123');
        $this->assertFalse($result);
    }

    public function testSearchChannelWithInvalidFeed(): void
    {
        $result = $this->model->searchChannel('invalid', 'test123');
        $this->assertFalse($result);
    }

    public function testSearchChannelWithEmptyArray(): void
    {
        $result = $this->model->searchChannel([], 'test123');
        $this->assertFalse($result);
    }

    public function testSearchChannelWithInvalidItems(): void
    {
        $feed = [
            'not_an_object',
            123,
            (object) ['no_id' => 'value'],
        ];

        $result = $this->model->searchChannel($feed, 'test123');
        $this->assertFalse($result);
    }

    public function testSearchChannelWithValidItemButDifferentId(): void
    {
        $feed = [
            (object) ['id' => 'different_id', 'title' => 'Test Video'],
        ];

        $result = $this->model->searchChannel($feed, 'target_id');
        $this->assertFalse($result);
    }

    public function testParseChannelMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'parseChannel'));
    }

    public function testParseChannelWithFalseFeed(): void
    {
        $result = $this->model->parseChannel(false);
        $this->assertFalse($result);
    }

    public function testParseChannelWithInvalidFeed(): void
    {
        $result = $this->model->parseChannel('invalid');
        $this->assertFalse($result);
    }

    public function testParseChannelWithEmptyArray(): void
    {
        $result = $this->model->parseChannel([]);
        $this->assertSame(0, $result);
    }

    public function testParseChannelWithInvalidItems(): void
    {
        $feed = [
            'not_an_object',
            123,
            (object) ['no_id' => 'value'],
        ];

        $result = $this->model->parseChannel($feed);
        $this->assertSame(0, $result);
    }

    public function testParseChannelReturnsIntegerOrFalse(): void
    {
        $result = $this->model->parseChannel([]);
        $this->assertTrue(is_int($result) || $result === false);
    }

    public function testSearchChannelReturnsBoolean(): void
    {
        $result = $this->model->searchChannel([], 'test123');
        $this->assertIsBool($result);
    }

    public function testParseChannelHandlesValidObjectWithoutExistingVideo(): void
    {
        $feed = [];

        $result = $this->model->parseChannel($feed);
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testSearchChannelHandlesValidObjectStructure(): void
    {
        $feed = [
            (object) [
                'id'              => 'test123',
                'title'           => 'Test Video',
                'upload_date'     => '2024-01-01T00:00:00Z',
                'thumbnail_large' => 'https://example.com/thumb.jpg',
                'url'             => 'https://vimeo.com/test123',
                'duration'        => 120,
                'width'           => 1920,
                'height'          => 1080,
                'description'     => 'Test description',
                'user_name'       => 'testuser',
                'user_url'        => 'https://vimeo.com/testuser',
            ],
        ];

        $result = $this->model->searchChannel($feed, 'test123');
        $this->assertIsBool($result);
    }

    public function testModelHandlesErrorConditionsGracefully(): void
    {
        $this->assertFalse($this->model->searchChannel(null, 'test'));
        $this->assertFalse($this->model->parseChannel(null));
    }

    public function testSearchChannelWithExistingVideo(): void
    {
        // Mock AggroModels to return true for checkVideo (video already exists)
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(true);

        $mockUtility = $this->createMock(UtilityModels::class);

        $model = new VimeoModels($mockAggro, $mockUtility);

        $feed = [
            (object) ['id' => 'existing_video_id', 'title' => 'Test Video'],
        ];

        // Video exists, so searchChannel should return false (not added)
        $result = $model->searchChannel($feed, 'existing_video_id');
        $this->assertFalse($result);
    }

    public function testParseChannelDoesNotLogForZeroVideos(): void
    {
        // Mock dependencies - no videos added so sendLog should not be called
        $mockAggro = $this->createMock(AggroModels::class);
        $mockAggro->method('checkVideo')->willReturn(true); // All videos exist

        $mockUtility = $this->createMock(UtilityModels::class);
        $mockUtility->expects($this->never())->method('sendLog');

        $model = new VimeoModels($mockAggro, $mockUtility);

        $result = $model->parseChannel([]);
        $this->assertSame(0, $result);
    }
}
