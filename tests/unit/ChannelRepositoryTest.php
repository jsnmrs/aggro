<?php

use App\Repositories\ChannelRepository;
use Tests\Support\RepositoryTestCase;

/**
 * @internal
 */
final class ChannelRepositoryTest extends RepositoryTestCase
{
    private ChannelRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ChannelRepository();
    }

    public function testGetChannelsReturnsStaleChannels()
    {
        // Arrange
        $staleChannel = $this->createTestChannel([
            'source_slug' => 'stale_channel',
            'source_type' => 'youtube',
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]);
        $freshChannel = $this->createTestChannel([
            'source_slug' => 'fresh_channel',
            'source_type' => 'youtube',
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
        ]);

        $this->db->table('aggro_sources')->insertBatch([$staleChannel, $freshChannel]);

        // Act
        $results = $this->repository->getChannels('30', 'youtube', '10');

        // Assert
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertEquals('stale_channel', $results[0]->source_slug);
    }

    public function testGetChannelsReturnsFalseWhenNoStaleChannels()
    {
        // Arrange
        $freshChannel = $this->createTestChannel([
            'source_type' => 'youtube',
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
        ]);

        $this->db->table('aggro_sources')->insert($freshChannel);

        // Act
        $results = $this->repository->getChannels('30', 'youtube', '10');

        // Assert
        $this->assertFalse($results);
    }

    public function testGetChannelsFiltersCorrectType()
    {
        // Arrange
        $youtubeChannel = $this->createTestChannel([
            'source_slug' => 'youtube_channel',
            'source_type' => 'youtube',
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]);
        $vimeoChannel = $this->createTestChannel([
            'source_slug' => 'vimeo_channel',
            'source_type' => 'vimeo',
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]);

        $this->db->table('aggro_sources')->insertBatch([$youtubeChannel, $vimeoChannel]);

        // Act
        $youtubeResults = $this->repository->getChannels('30', 'youtube', '10');
        $vimeoResults = $this->repository->getChannels('30', 'vimeo', '10');

        // Assert
        $this->assertIsArray($youtubeResults);
        $this->assertCount(1, $youtubeResults);
        $this->assertEquals('youtube_channel', $youtubeResults[0]->source_slug);

        $this->assertIsArray($vimeoResults);
        $this->assertCount(1, $vimeoResults);
        $this->assertEquals('vimeo_channel', $vimeoResults[0]->source_slug);
    }

    public function testGetChannelsRespectsLimit()
    {
        // Arrange
        $channels = [];
        for ($i = 1; $i <= 5; $i++) {
            $channels[] = $this->createTestChannel([
                'source_slug' => "channel_{$i}",
                'source_type' => 'youtube',
                'source_date_updated' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ]);
        }

        $this->db->table('aggro_sources')->insertBatch($channels);

        // Act
        $results = $this->repository->getChannels('30', 'youtube', '3');

        // Assert
        $this->assertIsArray($results);
        $this->assertCount(3, $results);
    }

    public function testGetChannelsOrdersByDateUpdated()
    {
        // Arrange
        $olderChannel = $this->createTestChannel([
            'source_slug' => 'older_channel',
            'source_type' => 'youtube',
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ]);
        $newerChannel = $this->createTestChannel([
            'source_slug' => 'newer_channel',
            'source_type' => 'youtube',
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]);

        $this->db->table('aggro_sources')->insertBatch([$newerChannel, $olderChannel]);

        // Act
        $results = $this->repository->getChannels('30', 'youtube', '10');

        // Assert
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        // Should return older channel first (ASC order)
        $this->assertEquals('older_channel', $results[0]->source_slug);
        $this->assertEquals('newer_channel', $results[1]->source_slug);
    }

    public function testUpdateChannelUpdatesTimestamp()
    {
        // Arrange
        $channelData = $this->createTestChannel([
            'source_slug' => 'test_channel',
            'source_date_updated' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ]);

        $this->db->table('aggro_sources')->insert($channelData);

        $originalTime = $channelData['source_date_updated'];

        // Act
        $this->repository->updateChannel('test_channel');

        // Assert
        $updatedChannel = $this->db->table('aggro_sources')
            ->where('source_slug', 'test_channel')
            ->get()
            ->getRowArray();

        $this->assertNotEquals($originalTime, $updatedChannel['source_date_updated']);
        
        // Should be updated to current time (within last minute)
        $timeDiff = time() - strtotime($updatedChannel['source_date_updated']);
        $this->assertLessThan(60, $timeDiff);
    }
}