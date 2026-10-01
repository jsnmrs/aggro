<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestLogger;

/**
 * Tests for the network block set in phpunit.xml.dist.
 *
 * @internal
 */
final class NetworkBlockTest extends CIUnitTestCase
{
    public function testOutboundRequestsAreBlocked(): void
    {
        // Guards the network block the rest of the suite relies on. A request
        // that reached YouTube would come back with a page and an HTTP status.
        helper('aggro');

        $httpStatus = null;
        $result     = fetch_url('https://www.youtube.com/', 'text', 0, $httpStatus);

        $this->assertSame(0, $httpStatus);
        $this->assertFalse($result);
        $this->assertTrue(TestLogger::didLog('warning', '127.0.0.1 port 1', false));
    }
}
