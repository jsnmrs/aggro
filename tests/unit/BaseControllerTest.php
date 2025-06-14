<?php

namespace Tests\Unit;

use App\Controllers\BaseController;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class TestableBaseController extends BaseController
{
    public function index()
    {
        return 'test';
    }

    public function testValidateVideoSlug($slug): bool
    {
        return $this->validateVideoSlug($slug);
    }

    public function testValidateYouTubeVideoId($videoId): bool
    {
        return $this->validateYouTubeVideoId($videoId);
    }

    public function testValidateVimeoVideoId($videoId): bool
    {
        return $this->validateVimeoVideoId($videoId);
    }

    public function testValidatePageNumber($page): bool
    {
        return $this->validatePageNumber($page);
    }
}

/**
 * @internal
 */
final class BaseControllerTest extends CIUnitTestCase
{
    protected TestableBaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TestableBaseController();
    }

    public function testControllerExtendsCodeIgniterController(): void
    {
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    public function testHelpersPropertyContainsViewHelper(): void
    {
        $reflection = new ReflectionClass($this->controller);
        $property   = $reflection->getProperty('helpers');
        $property->setAccessible(true);
        $helpers = $property->getValue($this->controller);

        $this->assertContains('view', $helpers);
    }

    public function testInitControllerSetsTimezone(): void
    {
        $request  = $this->createMock(IncomingRequest::class);
        $response = $this->createMock(ResponseInterface::class);
        $logger   = $this->createMock(LoggerInterface::class);

        $this->controller->initController($request, $response, $logger);

        $this->assertSame('America/New_York', date_default_timezone_get());
    }

    public function testValidateVideoSlugWithValidSlugs(): void
    {
        $this->assertTrue($this->controller->testValidateVideoSlug('valid-slug'));
        $this->assertTrue($this->controller->testValidateVideoSlug('slug_with_underscore'));
        $this->assertTrue($this->controller->testValidateVideoSlug('slug123'));
    }

    public function testValidateVideoSlugWithInvalidSlugs(): void
    {
        $this->assertFalse($this->controller->testValidateVideoSlug(''));
        $this->assertFalse($this->controller->testValidateVideoSlug(null));
        $this->assertFalse($this->controller->testValidateVideoSlug('slug with spaces'));
        $this->assertFalse($this->controller->testValidateVideoSlug('slug@with#special'));
        $this->assertFalse($this->controller->testValidateVideoSlug(str_repeat('a', 51)));
    }

    public function testValidateYouTubeVideoIdWithValidIds(): void
    {
        $this->assertTrue($this->controller->testValidateYouTubeVideoId('dQw4w9WgXcQ'));
        $this->assertTrue($this->controller->testValidateYouTubeVideoId('ABC123def45'));
        $this->assertTrue($this->controller->testValidateYouTubeVideoId('_-_-_-_-_-_'));
    }

    public function testValidateYouTubeVideoIdWithInvalidIds(): void
    {
        $this->assertFalse($this->controller->testValidateYouTubeVideoId(''));
        $this->assertFalse($this->controller->testValidateYouTubeVideoId(null));
        $this->assertFalse($this->controller->testValidateYouTubeVideoId('short'));
        $this->assertFalse($this->controller->testValidateYouTubeVideoId('toolongvideoid'));
        $this->assertFalse($this->controller->testValidateYouTubeVideoId('invalid@id!'));
    }

    public function testValidateVimeoVideoIdWithValidIds(): void
    {
        $this->assertTrue($this->controller->testValidateVimeoVideoId('123456'));
        $this->assertTrue($this->controller->testValidateVimeoVideoId('1234567'));
        $this->assertTrue($this->controller->testValidateVimeoVideoId('123456789'));
        $this->assertTrue($this->controller->testValidateVimeoVideoId('1234567890'));
    }

    public function testValidateVimeoVideoIdWithInvalidIds(): void
    {
        $this->assertFalse($this->controller->testValidateVimeoVideoId(''));
        $this->assertFalse($this->controller->testValidateVimeoVideoId(null));
        $this->assertFalse($this->controller->testValidateVimeoVideoId('12345'));
        $this->assertFalse($this->controller->testValidateVimeoVideoId('12345678901'));
        $this->assertFalse($this->controller->testValidateVimeoVideoId('abc123'));
    }

    public function testValidatePageNumberWithValidNumbers(): void
    {
        $this->assertTrue($this->controller->testValidatePageNumber(1));
        $this->assertTrue($this->controller->testValidatePageNumber('1'));
        $this->assertTrue($this->controller->testValidatePageNumber(100));
        $this->assertTrue($this->controller->testValidatePageNumber('9999'));
    }

    public function testValidatePageNumberWithInvalidNumbers(): void
    {
        $this->assertFalse($this->controller->testValidatePageNumber(0));
        $this->assertFalse($this->controller->testValidatePageNumber(-1));
        $this->assertFalse($this->controller->testValidatePageNumber(10001));
        $this->assertFalse($this->controller->testValidatePageNumber('abc'));
        $this->assertFalse($this->controller->testValidatePageNumber(null));
    }
}
