<?php

namespace Tests\Unit;

use App\Models\AggroModels;
use App\Repositories\ChannelRepository;
use App\Repositories\VideoRepository;
use App\Services\ArchiveService;
use App\Services\ThumbnailService;
use CodeIgniter\Model;
use ReflectionClass;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class AggroModelsTest extends DatabaseTestCase
{
    protected AggroModels $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new AggroModels();
    }

    public function testModelExtendsCodeIgniterModel(): void
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    public function testModelHasRequiredDependencies(): void
    {
        $reflection = new ReflectionClass($this->model);

        $videoRepoProperty = $reflection->getProperty('videoRepository');
        $videoRepoProperty->setAccessible(true);
        $this->assertInstanceOf(VideoRepository::class, $videoRepoProperty->getValue($this->model));

        $channelRepoProperty = $reflection->getProperty('channelRepository');
        $channelRepoProperty->setAccessible(true);
        $this->assertInstanceOf(ChannelRepository::class, $channelRepoProperty->getValue($this->model));

        $archiveServiceProperty = $reflection->getProperty('archiveService');
        $archiveServiceProperty->setAccessible(true);
        $this->assertInstanceOf(ArchiveService::class, $archiveServiceProperty->getValue($this->model));

        $thumbnailServiceProperty = $reflection->getProperty('thumbnailService');
        $thumbnailServiceProperty->setAccessible(true);
        $this->assertInstanceOf(ThumbnailService::class, $thumbnailServiceProperty->getValue($this->model));
    }

    public function testAddVideoMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'addVideo'));
    }

    public function testAddVideoCallsRepository(): void
    {
        $video = [
            'video_id'              => 'test123',
            'aggro_date_added'      => date('Y-m-d H:i:s'),
            'aggro_date_updated'    => date('Y-m-d H:i:s'),
            'video_date_uploaded'   => date('Y-m-d H:i:s'),
            'flag_archive'          => 0,
            'video_plays'           => 0,
            'video_title'           => 'Test Video',
            'video_thumbnail_url'   => 'https://example.com/thumb.jpg',
            'video_width'           => 1920,
            'video_height'          => 1080,
            'video_aspect_ratio'    => 16 / 9,
            'video_duration'        => 180,
            'video_source_id'       => 'channel123',
            'video_source_username' => 'test-channel',
            'video_source_url'      => 'https://example.com/channel',
            'video_type'            => 'youtube',
        ];

        $result = $this->model->addVideo($video);
        $this->assertIsBool($result);
    }

    public function testArchiveVideosMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'archiveVideos'));
    }

    public function testArchiveVideosCallsService(): void
    {
        $result = $this->model->archiveVideos();
        $this->assertIsBool($result);
    }

    public function testCheckThumbsMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'checkThumbs'));
    }

    public function testCheckThumbsCallsService(): void
    {
        $result = $this->model->checkThumbs();
        $this->assertIsBool($result);
    }

    public function testCheckVideoMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'checkVideo'));
    }

    public function testCheckVideoCallsRepository(): void
    {
        $result = $this->model->checkVideo('test123');
        $this->assertIsBool($result);
    }

    public function testCleanThumbsMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'cleanThumbs'));
    }

    public function testCleanThumbsCallsService(): void
    {
        $result = $this->model->cleanThumbs();
        $this->assertIsBool($result);
    }

    public function testGetChannelsMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getChannels'));
    }

    public function testGetChannelsCallsRepository(): void
    {
        $result = $this->model->getChannels('30', 'youtube', '10');
        $this->assertTrue(is_array($result) || $result === false);
    }

    public function testGetVideoMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getVideo'));
    }

    public function testGetVideoCallsRepository(): void
    {
        $result = $this->model->getVideo('test-slug');
        $this->assertTrue($result === false || is_array($result));
    }

    public function testGetVideosMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getVideos'));
    }

    public function testGetVideosCallsRepository(): void
    {
        $result = $this->model->getVideos('month', '10', '0');
        $this->assertIsArray($result);
    }

    public function testGetVideosTotalMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'getVideosTotal'));
    }

    public function testGetVideosTotalCallsRepository(): void
    {
        $result = $this->model->getVideosTotal();
        $this->assertIsInt($result);
    }

    public function testUpdateChannelMethodExists(): void
    {
        $this->assertTrue(method_exists($this->model, 'updateChannel'));
    }

    public function testUpdateChannelCallsRepository(): void
    {
        $this->expectNotToPerformAssertions();
        $this->model->updateChannel('test-channel');
    }
}
