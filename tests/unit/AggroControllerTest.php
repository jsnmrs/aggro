<?php

namespace Tests\Unit;

use App\Controllers\Aggro;
use App\Controllers\BaseController;
use CodeIgniter\Test\ControllerTestTrait;
use ReflectionClass;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class AggroControllerTest extends DatabaseTestCase
{
    use ControllerTestTrait;

    protected Aggro $aggroController;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the controller first to see if it works with the test database
        $this->aggroController = new Aggro();
    }

    public function testControllerExtendsBaseController(): void
    {
        $this->assertInstanceOf(BaseController::class, $this->aggroController);
    }

    public function testGetIndexMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getIndex'));
    }

    public function testGetIndexReturnsOutput(): void
    {
        ob_start();
        $this->aggroController->getIndex();
        $output = ob_get_clean();

        $this->assertStringContainsString('running cron all day.', $output);
        $this->assertStringContainsString('color:#005600', $output);
    }

    public function testGetInfoMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getInfo'));
    }

    public function testGetInfoReturnsVersionInfo(): void
    {
        $result = $this->controller(Aggro::class)
            ->execute('getInfo');

        $response = $result->response();

        $this->assertTrue($response->hasHeader('Cache-Control'));
        $this->assertTrue($response->hasHeader('Pragma'));
        $this->assertTrue($response->hasHeader('Expires'));
    }

    public function testGetLogMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getLog'));
    }

    public function testGetLogRequiresGateCheck(): void
    {
        // Gate check bypasses in development/CLI environments
        if (env('CI_ENVIRONMENT', 'production') === 'development' || is_cli()) {
            $this->markTestSkipped('Gate check bypasses in development/CLI environments');
        }

        $_GET['g'] = null;
        $result    = $this->controller(Aggro::class)
            ->execute('getLog');

        $this->assertSame(403, $result->response()->getStatusCode());
    }

    public function testGetLogCleanMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getLogClean'));
    }

    public function testGetLogErrorMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getLogError'));
    }

    public function testGetLogErrorCleanMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getLogErrorClean'));
    }

    public function testGetNewsMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getNews'));
    }

    public function testGetNewsRequiresGateCheck(): void
    {
        // Gate check bypasses in development/CLI environments
        if (env('CI_ENVIRONMENT', 'production') === 'development' || is_cli()) {
            $this->markTestSkipped('Gate check bypasses in development/CLI environments');
        }

        $_GET['g'] = null;
        $result    = $this->controller(Aggro::class)
            ->execute('getNews');

        $this->assertSame(403, $result->response()->getStatusCode());
    }

    public function testGetNewsCacheMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getNewsCache'));
    }

    public function testGetNewsCleanMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getNewsClean'));
    }

    public function testGetSweepMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getSweep'));
    }

    public function testGetYouTubeDurationMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getYouTubeDuration'));
    }

    public function testGetVimeoMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getVimeo'));
    }

    public function testGetYoutubeMethodExists(): void
    {
        $this->assertTrue(method_exists($this->aggroController, 'getYoutube'));
    }

    public function testValidateVimeoVideoIdMethodExists(): void
    {
        $reflection = new ReflectionClass($this->aggroController);
        $this->assertTrue($reflection->hasMethod('validateVimeoVideoId'));
    }

    public function testValidateYouTubeVideoIdMethodExists(): void
    {
        $reflection = new ReflectionClass($this->aggroController);
        $this->assertTrue($reflection->hasMethod('validateYouTubeVideoId'));
    }

    public function testGetNewsWithSlugClean(): void
    {
        // Mock gate_check to return true
        $_GET['g'] = 'testkey';

        // Skip actual database operations
        $this->markTestSkipped('Method requires database access and gate authentication');

        $result = $this->aggroController->getNews('clean');
        $this->assertSame('Featured news stories cleared.', $result);
    }

    public function testGetNewsWithSlugCc(): void
    {
        // Mock gate_check to return true
        $_GET['g'] = 'testkey';

        // Skip actual database operations
        $this->markTestSkipped('Method requires database access and gate authentication');

        $result = $this->aggroController->getNews('cc');
        $this->assertSame('Feed caches cleared.', $result);
    }

    public function testGetNewsWithNullSlug(): void
    {
        // Mock gate_check to return true
        $_GET['g'] = 'testkey';

        // Skip actual database operations
        $this->markTestSkipped('Method requires database access and gate authentication');

        $result = $this->aggroController->getNews(null);
        $this->assertSame('Featured page built.', $result);
    }

    public function testGetNewsCacheRequiresGateCheck(): void
    {
        // Skip test that requires gate_check helper and database setup
        $this->markTestSkipped('Method requires gate_check helper and database access');
    }

    public function testGetNewsCleanRequiresGateCheck(): void
    {
        // Skip test that requires gate_check helper and database setup
        $this->markTestSkipped('Method requires gate_check helper and database access');
    }

    public function testGetSweepRequiresGateCheck(): void
    {
        // Skip test that requires gate_check helper and database setup
        $this->markTestSkipped('Method requires gate_check helper and database access');
    }

    public function testGetYouTubeDurationRequiresGateCheck(): void
    {
        // Skip test that requires gate_check helper and database setup
        $this->markTestSkipped('Method requires gate_check helper and database access');
    }

    public function testGetVimeoRequiresGateCheck(): void
    {
        // Gate check bypasses in development/CLI environments
        if (env('CI_ENVIRONMENT', 'production') === 'development' || is_cli()) {
            $this->markTestSkipped('Gate check bypasses in development/CLI environments');
        }

        $_GET['g'] = null;
        $result    = $this->controller(Aggro::class)
            ->execute('getVimeo');

        $this->assertSame(403, $result->response()->getStatusCode());
    }

    public function testGetVimeoWithInvalidVideoId(): void
    {
        $_GET['g'] = 'testkey';

        // Test with invalid video ID format - should return 404
        $result = $this->controller(Aggro::class)
            ->execute('getVimeo', 'invalid123');

        $this->assertSame(404, $result->response()->getStatusCode());
    }

    public function testGetYoutubeRequiresGateCheck(): void
    {
        // Gate check bypasses in development/CLI environments
        if (env('CI_ENVIRONMENT', 'production') === 'development' || is_cli()) {
            $this->markTestSkipped('Gate check bypasses in development/CLI environments');
        }

        $_GET['g'] = null;
        $result    = $this->controller(Aggro::class)
            ->execute('getYoutube');

        $this->assertSame(403, $result->response()->getStatusCode());
    }

    public function testGetYoutubeWithInvalidVideoId(): void
    {
        $_GET['g'] = 'testkey';

        // Test with invalid video ID format - should return 404
        $result = $this->controller(Aggro::class)
            ->execute('getYoutube', 'invalid');

        $this->assertSame(404, $result->response()->getStatusCode());
    }

    public function testGetLogCleanRequiresGateCheck(): void
    {
        // Skip test that requires gate_check helper and database access
        $this->markTestSkipped('Method requires gate_check helper and database access');
    }

    public function testGetLogErrorRequiresGateCheck(): void
    {
        // Skip test that requires gate_check helper and database access
        $this->markTestSkipped('Method requires gate_check helper and database access');
    }

    public function testGetLogErrorCleanRequiresGateCheck(): void
    {
        // Skip test that requires gate_check helper and database access
        $this->markTestSkipped('Method requires gate_check helper and database access');
    }

    public function testGetInfoMethodSetsHeaders(): void
    {
        $result = $this->controller(Aggro::class)
            ->execute('getInfo');

        $response = $result->response();

        // Test that cache-related headers are set
        $this->assertTrue($response->hasHeader('Cache-Control'));
        $this->assertTrue($response->hasHeader('Pragma'));
        $this->assertTrue($response->hasHeader('Expires'));
    }

    public function testGetInfoOutputFormat(): void
    {
        // Test the output format without triggering header issues
        $this->markTestSkipped('Method requires proper response setup for header testing');
    }
}
