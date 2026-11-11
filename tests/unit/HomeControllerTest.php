<?php

namespace Tests\Unit;

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

    public function testGetIndexReturnsWelcomeView(): void
    {
        $result = $this->controller->getIndex();

        $this->assertIsString($result);
        $this->assertStringContainsString('<!DOCTYPE html>', $result);
    }
}
