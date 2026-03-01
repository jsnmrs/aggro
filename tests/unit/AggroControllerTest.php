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

    public function testGetLogExecutesInCliMode(): void
    {
        // In CLI mode gate_check() returns true, so getLog should succeed
        $result = $this->controller(Aggro::class)
            ->execute('getLog');

        $this->assertTrue($result->isOK());
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

    public function testGetNewsExecutesInCliMode(): void
    {
        // In CLI mode gate_check() returns true, so getNews should succeed
        $result = $this->controller(Aggro::class)
            ->execute('getNews');

        $this->assertTrue($result->isOK());
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
        // In CLI mode gate_check() returns true, so we can test the clean path
        $result = $this->controller(Aggro::class)
            ->execute('getNews', 'clean');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Featured news stories cleared.', $body);
    }

    public function testGetNewsWithSlugCc(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getNews', 'cc');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Feed caches cleared.', $body);
    }

    public function testGetNewsWithNullSlug(): void
    {
        // In CLI mode gate_check() returns true, null slug triggers featuredBuilder
        $result = $this->controller(Aggro::class)
            ->execute('getNews');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Featured page built.', $body);
    }

    public function testGetNewsWithInvalidSlug(): void
    {
        // Invalid slug should return 404
        $result = $this->controller(Aggro::class)
            ->execute('getNews', 'nonexistent');

        $this->assertSame(404, $result->response()->getStatusCode());
    }

    public function testGetNewsCacheReturnsExpectedMessage(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getNewsCache');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Feed caches cleared.', $body);
    }

    public function testGetNewsCleanReturnsExpectedMessage(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getNewsClean');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Featured news stories cleared.', $body);
    }

    public function testGetSweepExecutesSuccessfully(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getSweep');

        // getSweep should complete without errors
        $this->assertTrue($result->isOK() || $result->response()->getStatusCode() === 200);
    }

    public function testGetYouTubeDurationExecutesSuccessfully(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getYouTubeDuration');

        $this->assertTrue($result->isOK() || $result->response()->getStatusCode() === 200);
    }

    public function testGetVimeoWithNullVideoIdExecutes(): void
    {
        // In CLI mode gate_check() returns true, null videoID fetches channels
        $result = $this->controller(Aggro::class)
            ->execute('getVimeo');

        // Returns false (no stale channels) or completes successfully
        $this->assertTrue(
            $result->isOK() || $result->response()->getStatusCode() === 200,
        );
    }

    public function testGetVimeoWithInvalidVideoId(): void
    {
        $_GET['g'] = 'testkey';

        // Test with invalid video ID format - should return 404
        $result = $this->controller(Aggro::class)
            ->execute('getVimeo', 'invalid123');

        $this->assertSame(404, $result->response()->getStatusCode());
    }

    public function testGetYoutubeWithNullVideoIdExecutes(): void
    {
        // In CLI mode gate_check() returns true, null videoID fetches channels
        $result = $this->controller(Aggro::class)
            ->execute('getYoutube');

        // Returns false (no stale channels) or completes successfully
        $this->assertTrue(
            $result->isOK() || $result->response()->getStatusCode() === 200,
        );
    }

    public function testGetYoutubeWithInvalidVideoId(): void
    {
        $_GET['g'] = 'testkey';

        // Test with invalid video ID format - should return 404
        $result = $this->controller(Aggro::class)
            ->execute('getYoutube', 'invalid');

        $this->assertSame(404, $result->response()->getStatusCode());
    }

    public function testGetLogCleanExecutesSuccessfully(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getLogClean');

        // Should redirect to /aggro/log
        $this->assertTrue(
            $result->response()->hasHeader('Location') || $result->isRedirect() || $result->isOK(),
        );
    }

    public function testGetLogErrorExecutesSuccessfully(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getLogError');

        $this->assertTrue($result->isOK());
    }

    public function testGetLogErrorCleanExecutesSuccessfully(): void
    {
        // In CLI mode gate_check() returns true
        $result = $this->controller(Aggro::class)
            ->execute('getLogErrorClean');

        // Should redirect to /aggro/log/error
        $this->assertTrue(
            $result->response()->hasHeader('Location') || $result->isRedirect() || $result->isOK(),
        );
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

    public function testGetInfoOutputContainsVersionInfo(): void
    {
        $result = $this->controller(Aggro::class)
            ->execute('getInfo');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('CI ', $body);
        $this->assertStringContainsString('PHP ', $body);
    }
}
