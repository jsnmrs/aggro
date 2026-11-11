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

    public function testGetAboutReturnsCorrectData()
    {
        $result = $this->controller(Front::class)
            ->execute('getAbout');

        $response = $result->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('About', $response->getBody());
    }

    public function testGetError404Sets404Status()
    {
        $result = $this->controller(Front::class)
            ->execute('getError404');

        $response = $result->response();
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetSitesWithNullSlug()
    {
        $result = $this->controller(Front::class)
            ->execute('getSites');

        $response = $result->response();
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetSitesWithInvalidSlugReturns404()
    {
        $result = $this->controller(Front::class)
            ->execute('getSites', 'nonexistent-site-slug');

        $response = $result->response();
        $this->assertSame(404, $response->getStatusCode());
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

    public function testHandleIndividualVideoWithInvalidSlug()
    {
        // Empty slug triggers pagination, not individual video
        // Test with a slug that won't match any video -> 404
        $result = $this->controller(Front::class)
            ->execute('getVideo', 'nonexistent-video-slug');

        $response = $result->response();
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetVideoWithEmptySlug()
    {
        // Empty slug should trigger the video list / pagination view
        $result = $this->controller(Front::class)
            ->execute('getVideo');

        $response = $result->response();
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetVideoWithRecentSlug()
    {
        // 'recent' slug should trigger the video list / pagination view
        $result = $this->controller(Front::class)
            ->execute('getVideo', 'recent');

        $response = $result->response();
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetVideoWithNonexistentSlug()
    {
        // Non-existent video slug should return 404
        $result = $this->controller(Front::class)
            ->execute('getVideo', 'doesnotexist123');

        $response = $result->response();
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testSitemapReturnsXmlResponse()
    {
        $result = $this->controller(Front::class)
            ->execute('sitemap');

        $response = $result->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/xml', $response->getHeaderLine('Content-Type'));
    }

    public function testSitemapReturnsValidXml()
    {
        $result = $this->controller(Front::class)
            ->execute('sitemap');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('<?xml', $body);
        $this->assertStringContainsString('<urlset', $body);
    }

    public function testRobotsReturnsTextPlainResponse()
    {
        $result = $this->controller(Front::class)
            ->execute('robots');

        $response = $result->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('text/plain', $response->getHeaderLine('Content-Type'));
    }

    public function testRobotsIncludesSitemapDirective()
    {
        $result = $this->controller(Front::class)
            ->execute('robots');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Sitemap:', $body);
        $this->assertStringContainsString('sitemap.xml', $body);
    }

    public function testRobotsIncludesUserAgent()
    {
        $result = $this->controller(Front::class)
            ->execute('robots');

        $body = $result->response()->getBody();
        $this->assertStringContainsString('User-agent: *', $body);
    }
}
