<?php

namespace Tests\app\Filters;

use App\Filters\CustomCSRF;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Security;

/**
 * Test CSRF protection is properly configured
 *
 * @internal
 */
final class CustomCSRFTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable CSRF for testing
        $config                 = new Security();
        $config->csrfProtection = 'cookie';
        $config->tokenRandomize = true;
        Services::injectMock('security', $config);
    }

    public function testCSRFFilterExists()
    {
        $filter = new CustomCSRF();
        $this->assertInstanceOf(CustomCSRF::class, $filter);
    }

    public function testCSRFEnabledInConfig()
    {
        $filters = config('Filters');
        $this->assertContains('csrf', $filters->globals['before']);
    }

    public function testCSRFUsesCustomFilter()
    {
        $filters = config('Filters');
        $this->assertSame(CustomCSRF::class, $filters->aliases['csrf']);
    }

    public function testStateChangingRoutesRequirePOST()
    {
        // Skip this test for now as route testing requires more setup
        $this->markTestSkipped('Route testing requires additional setup');
    }

    public function testCSRFTokenConfiguration()
    {
        $security = config('Security');

        // Verify CSRF is properly configured
        $this->assertSame('cookie', $security->csrfProtection);
        $this->assertTrue($security->tokenRandomize);
        $this->assertSame('aggro_security_token', $security->tokenName);
        $this->assertSame('aggro_security_cookie', $security->cookieName);
        $this->assertTrue($security->regenerate);
    }
}
