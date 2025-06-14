<?php

namespace Tests\Unit;

use App\Controllers\BaseController;
use App\Controllers\Home;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HomeControllerTest extends CIUnitTestCase
{
    protected Home $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new Home();
    }

    public function testControllerExtendsBaseController(): void
    {
        $this->assertInstanceOf(BaseController::class, $this->controller);
    }

    public function testGetIndexMethodExists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'getIndex'));
    }

    public function testGetIndexReturnsWelcomeView(): void
    {
        $result = $this->controller->getIndex();

        $this->assertIsString($result);
        $this->assertStringContainsString('<!doctype html>', $result);
    }
}
