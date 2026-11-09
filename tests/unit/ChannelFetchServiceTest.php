<?php

use App\Models\VimeoModels;
use App\Models\YoutubeModels;
use App\Services\ChannelFetchService;
use Tests\Support\ServiceTestCase;

/**
 * @internal
 */
final class ChannelFetchServiceTest extends ServiceTestCase
{
    private ChannelFetchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChannelFetchService();
    }

    public function testProcessStaleVimeoChannelsWithEmptyArrayProducesNoOutput(): void
    {
        ob_start();
        $this->service->processStaleVimeoChannels([]);
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testProcessStaleYoutubeChannelsWithEmptyArrayProducesNoOutput(): void
    {
        ob_start();
        $this->service->processStaleYoutubeChannels([]);
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testAddVimeoVideoReportsAddedWhenVideoIsInFeed(): void
    {
        $service = $this->serviceWithLookups('bmxuser', true);

        ob_start();
        $result = $service->addVimeoVideo('76979871');
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame("\nAdded https://vimeo.com/76979871 from bmxuser.\n", $output);
    }

    public function testAddVimeoVideoReportsNotFoundWhenVideoIsNotInFeed(): void
    {
        $service = $this->serviceWithLookups('bmxuser', false);

        ob_start();
        $result = $service->addVimeoVideo('76979871');
        $output = ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame("\nhttps://vimeo.com/76979871 was not found in the feed for bmxuser.\n", $output);
    }

    public function testAddVimeoVideoReturnsNullWhenLookupFails(): void
    {
        $service = $this->serviceWithLookups(false, true);

        ob_start();
        $result = $service->addVimeoVideo('76979871');
        $output = ob_get_clean();

        $this->assertNull($result);
        $this->assertSame("\nCould not look up https://vimeo.com/76979871.\n", $output);
    }

    public function testAddYoutubeVideoReportsAddedWhenVideoIsInFeed(): void
    {
        $service = $this->serviceWithLookups('UCtestchannel', true);

        ob_start();
        $result = $service->addYoutubeVideo('dQw4w9WgXcQ');
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertSame("\nAdded https://www.youtube.com/watch?v=dQw4w9WgXcQ from UCtestchannel.\n", $output);
    }

    public function testAddYoutubeVideoReportsNotFoundWhenVideoIsNotInFeed(): void
    {
        $service = $this->serviceWithLookups('UCtestchannel', false);

        ob_start();
        $result = $service->addYoutubeVideo('dQw4w9WgXcQ');
        $output = ob_get_clean();

        $this->assertFalse($result);
        $this->assertSame("\nhttps://www.youtube.com/watch?v=dQw4w9WgXcQ was not found in the feed for UCtestchannel.\n", $output);
    }

    public function testAddYoutubeVideoReturnsNullWhenLookupFails(): void
    {
        $service = $this->serviceWithLookups(false, true);

        ob_start();
        $result = $service->addYoutubeVideo('dQw4w9WgXcQ');
        $output = ob_get_clean();

        $this->assertNull($result);
        $this->assertSame("\nCould not look up https://www.youtube.com/watch?v=dQw4w9WgXcQ.\n", $output);
    }

    /**
     * Build a service whose network lookups and channel searches are canned.
     *
     * @param false|string $source Source ID the lookup returns, or false when it fails
     * @param bool         $found  Whether the video is found in (and added from) the feed
     */
    private function serviceWithLookups(false|string $source, bool $found): ChannelFetchService
    {
        $vimeoModel = $this->createMock(VimeoModels::class);
        $vimeoModel->method('searchChannel')->willReturn($found);

        $youtubeModel = $this->createMock(YoutubeModels::class);
        $youtubeModel->method('searchChannel')->willReturn($found);

        return new class ($vimeoModel, $youtubeModel, $source) extends ChannelFetchService {
            public function __construct(VimeoModels $vimeoModel, YoutubeModels $youtubeModel, private readonly false|string $source)
            {
                parent::__construct();
                $this->vimeoModel   = $vimeoModel;
                $this->youtubeModel = $youtubeModel;
            }

            protected function fetchVimeoSource(string $videoID)
            {
                return $this->source;
            }

            protected function fetchVimeoFeed(string $sourceID)
            {
                return [];
            }

            protected function fetchYoutubeSource(string $videoID)
            {
                return $this->source;
            }

            protected function fetchYoutubeFeed(string $sourceID)
            {
                return new stdClass();
            }
        };
    }
}
