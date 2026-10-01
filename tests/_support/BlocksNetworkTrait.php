<?php

namespace Tests\Support;

/**
 * Keeps tests from reaching the network.
 *
 * curl honors the proxy environment variables, so pointing them at a
 * closed local port makes every fetch_url() and SimplePie request fail
 * at once without leaving the machine.
 */
trait BlocksNetworkTrait
{
    protected function setUpBlocksNetworkTrait(): void
    {
        putenv('http_proxy=http://127.0.0.1:1');
        putenv('https_proxy=http://127.0.0.1:1');
    }

    protected function tearDownBlocksNetworkTrait(): void
    {
        putenv('http_proxy');
        putenv('https_proxy');
    }
}
