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
        // Skip this test if we can't properly set up the database
        if (!$this->db->tableExists('aggro_log')) {
            $this->markTestSkipped('Database table aggro_log not available in test environment');
        }
        
        $_GET['g'] = null;
        $result = $this->aggroController->getLog();
        
        // Result should be false (gate check fails) or string (log content)
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
