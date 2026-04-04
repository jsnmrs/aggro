<?php

namespace Tests\Unit;

use App\Filters\BasicAuth;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * Test subclass that overrides isCli() so the filter can be exercised
 * in the PHPUnit CLI environment without short-circuiting.
 */
class TestableBasicAuth extends BasicAuth
{
    private bool $cli;

    public function __construct(?string $environment = null, bool $isCli = false)
    {
        parent::__construct($environment);
        $this->cli = $isCli;
    }

    protected function isCli(): bool
    {
        return $this->cli;
    }
}

/**
 * @internal
 */
final class BasicAuthTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear any auth-related server vars
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up env overrides
        putenv('BASIC_AUTH_USER');
        putenv('BASIC_AUTH_PASS');
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    }

    private function createMockRequest(): IncomingRequest
    {
        return $this->createMock(IncomingRequest::class);
    }

    private function setEnvCredentials(string $user = 'testuser', string $pass = 'testpass'): void
    {
        putenv("BASIC_AUTH_USER={$user}");
        putenv("BASIC_AUTH_PASS={$pass}");
    }

    private function setServerCredentials(string $user = 'testuser', string $pass = 'testpass'): void
    {
        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW']   = $pass;
    }

    public function testSkipsWhenNotDevelopmentEnvironment(): void
    {
        $this->setEnvCredentials();
        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('production');
        $result = $filter->before($request);

        $this->assertNull($result, 'Should skip auth in production environment');
    }

    public function testSkipsInTestingEnvironment(): void
    {
        $this->setEnvCredentials();
        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('testing');
        $result = $filter->before($request);

        $this->assertNull($result, 'Should skip auth in testing environment');
    }

    public function testSkipsForCliRequests(): void
    {
        $this->setEnvCredentials();
        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('development', isCli: true);
        $result = $filter->before($request);

        $this->assertNull($result, 'Should skip auth for CLI requests');
    }

    public function testSkipsWhenEnvVarsAreMissing(): void
    {
        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('development');
        $result = $filter->before($request);

        $this->assertNull($result, 'Should skip auth when env vars are missing (fail-open)');
    }

    public function testSkipsWhenOnlyUserIsSet(): void
    {
        putenv('BASIC_AUTH_USER=testuser');
        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('development');
        $result = $filter->before($request);

        $this->assertNull($result, 'Should skip auth when only user env var is set');
    }

    public function testSkipsWhenOnlyPassIsSet(): void
    {
        putenv('BASIC_AUTH_PASS=testpass');
        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('development');
        $result = $filter->before($request);

        $this->assertNull($result, 'Should skip auth when only pass env var is set');
    }

    public function testReturns401WhenNoCredentialsProvided(): void
    {
        $this->setEnvCredentials();
        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('development');
        $result = $filter->before($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(401, $result->getStatusCode());
        $this->assertStringContainsString('Basic', $result->getHeaderLine('WWW-Authenticate'));
    }

    public function testReturns401WhenWrongCredentialsProvided(): void
    {
        $this->setEnvCredentials('testuser', 'testpass');
        $this->setServerCredentials('wronguser', 'wrongpass');

        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('development');
        $result = $filter->before($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(401, $result->getStatusCode());
    }

    public function testReturns401WhenWrongPasswordProvided(): void
    {
        $this->setEnvCredentials('testuser', 'testpass');
        $this->setServerCredentials('testuser', 'wrongpass');

        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('development');
        $result = $filter->before($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(401, $result->getStatusCode());
    }

    public function testAllowsAccessWithCorrectCredentials(): void
    {
        $this->setEnvCredentials('testuser', 'testpass');
        $this->setServerCredentials('testuser', 'testpass');

        $request = $this->createMockRequest();

        $filter = new TestableBasicAuth('development');
        $result = $filter->before($request);

        $this->assertNull($result, 'Should allow access with correct credentials');
    }

    public function testAfterReturnsNull(): void
    {
        $request  = $this->createMockRequest();
        $response = new Response(new App());

        $filter = new TestableBasicAuth();
        $result = $filter->after($request, $response);

        $this->assertNull($result);
    }
}
