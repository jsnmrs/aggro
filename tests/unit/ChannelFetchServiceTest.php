<?php

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
}
