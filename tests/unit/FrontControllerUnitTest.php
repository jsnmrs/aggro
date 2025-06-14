<?php

use App\Controllers\Front;
use CodeIgniter\Controller;
use CodeIgniter\Test\ControllerTestTrait;
use Tests\Support\RepositoryTestCase;

/**
 * Unit tests for Front controller.
 *
 * This test replaces the previously skipped feature test that had database isolation issues.
 * Instead of testing the full HTTP request cycle, we test the controller methods directly.
 *
 * @internal
 */
final class FrontControllerUnitTest extends RepositoryTestCase
{
    use ControllerTestTrait;

    private Front $frontController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->frontController = new Front();
    }

    public function testGetIndexMethodExists()
    {
        // Test that the getIndex method exists and is callable
        // This verifies the home page endpoint has the expected method
        $this->assertTrue(method_exists($this->frontController, 'getIndex'));
        $this->assertIsCallable([$this->frontController, 'getIndex']);
    }

    public function testGetFeaturedMethodExists()
    {
        // Test that the getFeatured method exists and is callable
        // This verifies the featured page endpoint has the expected method
        $this->assertTrue(method_exists($this->frontController, 'getFeatured'));
        $this->assertIsCallable([$this->frontController, 'getFeatured']);
    }

    public function testGetIndexCallsGetFeatured()
    {
        // Test that getIndex() calls getFeatured() by checking method relationships
        // We use reflection to verify the method behavior without executing it

        $reflection  = new ReflectionClass($this->frontController);
        $indexMethod = $reflection->getMethod('getIndex');

        // Get the source code of the method to verify it calls getFeatured
        $filename  = $reflection->getFileName();
        $startLine = $indexMethod->getStartLine();
        $endLine   = $indexMethod->getEndLine();

        $source     = file($filename);
        $methodCode = implode('', array_slice($source, $startLine - 1, $endLine - $startLine + 1));

        // Assert that the method calls getFeatured
        $this->assertStringContainsString('getFeatured', $methodCode);
    }

    public function testControllerExtendsBaseController()
    {
        // Verify the controller inheritance is correct
        $this->assertInstanceOf(Controller::class, $this->frontController);
    }

    public function testControllerHasExpectedMethods()
    {
        // Test that all expected public methods exist
        $expectedMethods = ['getIndex', 'getFeatured', 'getAbout', 'getError404', 'getSites', 'getStream', 'getVideo'];

        foreach ($expectedMethods as $method) {
            $this->assertTrue(
                method_exists($this->frontController, $method),
                "Method {$method} should exist on Front controller",
            );
        }
    }

    public function testGetAboutMethodExists()
    {
        $this->assertTrue(method_exists($this->frontController, 'getAbout'));
        $this->assertIsCallable([$this->frontController, 'getAbout']);
    }

    public function testGetAboutReturnsCorrectData()
    {
        // Skip actual view rendering but test data preparation
        $this->markTestSkipped('Method requires view rendering which needs full framework setup');

        $result = $this->frontController->getAbout();
        // Would test that view data contains title: 'About' and slug: 'about'
    }

    public function testGetError404MethodExists()
    {
        $this->assertTrue(method_exists($this->frontController, 'getError404'));
        $this->assertIsCallable([$this->frontController, 'getError404']);
    }

    public function testGetError404Sets404Status()
    {
        $result = $this->controller(Front::class)
            ->execute('getError404');

        $response = $result->response();
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetSitesMethodExists()
    {
        $this->assertTrue(method_exists($this->frontController, 'getSites'));
        $this->assertIsCallable([$this->frontController, 'getSites']);
    }

    public function testGetSitesWithNullSlug()
    {
        // Skip database operations
        $this->markTestSkipped('Method requires database access');

        $result = $this->frontController->getSites(null);
        // Would test that it returns sites list view
    }

    public function testGetSitesWithValidSlug()
    {
        // Skip database operations
        $this->markTestSkipped('Method requires database access');

        $result = $this->frontController->getSites('validslug');
        // Would test that it returns site detail view or 404
    }

    public function testGetStreamMethodExists()
    {
        $this->assertTrue(method_exists($this->frontController, 'getStream'));
        $this->assertIsCallable([$this->frontController, 'getStream']);
    }

    public function testGetVideoMethodExists()
    {
        $this->assertTrue(method_exists($this->frontController, 'getVideo'));
        $this->assertIsCallable([$this->frontController, 'getVideo']);
    }

    public function testSanitizeSlugMethodExists()
    {
        $reflection = new ReflectionClass($this->frontController);
        $this->assertTrue($reflection->hasMethod('sanitizeSlug'));
    }

    public function testSanitizeSlugCleansInput()
    {
        $reflection = new ReflectionClass($this->frontController);
        $method     = $reflection->getMethod('sanitizeSlug');
        $method->setAccessible(true);

        // Test various input scenarios
        $this->assertSame('valid-slug', $method->invokeArgs($this->frontController, ['valid-slug']));
        $this->assertSame('valid-slug123', $method->invokeArgs($this->frontController, ['valid-slug123']));
        $this->assertSame('', $method->invokeArgs($this->frontController, [null]));
        $this->assertSame('', $method->invokeArgs($this->frontController, ['']));
        $this->assertSame('validslug', $method->invokeArgs($this->frontController, ['valid@#$slug']));
    }

    public function testIsVideoListRequestMethodExists()
    {
        $reflection = new ReflectionClass($this->frontController);
        $this->assertTrue($reflection->hasMethod('isVideoListRequest'));
    }

    public function testIsVideoListRequestLogic()
    {
        $reflection = new ReflectionClass($this->frontController);
        $method     = $reflection->getMethod('isVideoListRequest');
        $method->setAccessible(true);

        // Test different slug scenarios
        $this->assertTrue($method->invokeArgs($this->frontController, ['']));
        $this->assertTrue($method->invokeArgs($this->frontController, ['recent']));
        $this->assertFalse($method->invokeArgs($this->frontController, ['specific-video']));
        $this->assertFalse($method->invokeArgs($this->frontController, ['anothervideo']));
    }

    public function testHandleVideosPaginationMethodExists()
    {
        $reflection = new ReflectionClass($this->frontController);
        $this->assertTrue($reflection->hasMethod('handleVideosPagination'));
    }

    public function testHandleIndividualVideoMethodExists()
    {
        $reflection = new ReflectionClass($this->frontController);
        $this->assertTrue($reflection->hasMethod('handleIndividualVideo'));
    }

    public function testHandleIndividualVideoWithInvalidSlug()
    {
        // Skip database operations but test validation logic
        $this->markTestSkipped('Method requires database access and full framework setup');

        $reflection = new ReflectionClass($this->frontController);
        $method     = $reflection->getMethod('handleIndividualVideo');
        $method->setAccessible(true);

        // Test with invalid slug format
        $result = $method->invokeArgs($this->frontController, ['']);
        // Would expect 404 response
    }

    public function testGetVideoWithEmptySlug()
    {
        // Skip database operations
        $this->markTestSkipped('Method requires database access');

        $result = $this->frontController->getVideo('');
        // Would test pagination flow
    }

    public function testGetVideoWithRecentSlug()
    {
        // Skip database operations
        $this->markTestSkipped('Method requires database access');

        $result = $this->frontController->getVideo('recent');
        // Would test pagination flow
    }

    public function testGetVideoWithSpecificSlug()
    {
        // Skip database operations
        $this->markTestSkipped('Method requires database access');

        $result = $this->frontController->getVideo('specific-video-slug');
        // Would test individual video display flow
    }
}
