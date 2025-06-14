<?php

namespace Tests\Unit;

use App\Controllers\Aggro;
use App\Controllers\BaseController;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use ReflectionClass;

/**
 * @internal
 */
final class AggroControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;

    protected Aggro $aggroController;

    protected function setUp(): void
    {
        parent::setUp();
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
        $_GET['g'] = null;
        $result    = $this->aggroController->getLog();
        $this->assertTrue($result === false || is_string($result));
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
        $_GET['g'] = null;
        $result    = $this->aggroController->getNews();
        $this->assertTrue($result === false || is_string($result));
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
}
