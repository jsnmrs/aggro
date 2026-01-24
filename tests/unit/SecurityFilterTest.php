<?php

namespace Tests\Unit;

use App\Filters\SecurityFilter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SecurityFilterTest extends CIUnitTestCase
{
    private SecurityFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new SecurityFilter();
    }

    /**
     * Creates a mock request with the specified method and content type.
     */
    private function createMockRequest(string $method, ?string $contentType = null): IncomingRequest
    {
        $request = $this->createMock(IncomingRequest::class);
        $request->method('getMethod')->willReturn($method);

        if ($contentType !== null) {
            $request->method('getHeaderLine')
                ->with('Content-Type')
                ->willReturn($contentType);
        } else {
            $request->method('getHeaderLine')
                ->with('Content-Type')
                ->willReturn('');
        }

        return $request;
    }

    public function testPostWithMultipartFormDataWithBoundaryIsAccepted(): void
    {
        $contentType = 'multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW';
        $request     = $this->createMockRequest('post', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'multipart/form-data with boundary should be accepted');
    }

    public function testPostWithMultipartFormDataWithoutBoundaryIsAccepted(): void
    {
        $contentType = 'multipart/form-data';
        $request     = $this->createMockRequest('post', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'multipart/form-data without boundary should be accepted');
    }

    public function testPostWithApplicationJsonIsAccepted(): void
    {
        $contentType = 'application/json';
        $request     = $this->createMockRequest('post', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'application/json should be accepted');
    }

    public function testPostWithApplicationJsonCharsetIsAccepted(): void
    {
        $contentType = 'application/json; charset=utf-8';
        $request     = $this->createMockRequest('post', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'application/json with charset should be accepted');
    }

    public function testPostWithFormUrlEncodedIsAccepted(): void
    {
        $contentType = 'application/x-www-form-urlencoded';
        $request     = $this->createMockRequest('post', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'application/x-www-form-urlencoded should be accepted');
    }

    public function testPostWithInvalidContentTypeIsRejected(): void
    {
        $contentType = 'text/plain';
        $request     = $this->createMockRequest('post', $contentType);

        $result = $this->filter->before($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(415, $result->getStatusCode());
    }

    public function testGetRequestBypassesContentTypeCheck(): void
    {
        $request = $this->createMockRequest('get');

        $result = $this->filter->before($request);

        $this->assertNull($result, 'GET requests should bypass content-type check');
    }

    public function testPutRequestBypassesContentTypeCheck(): void
    {
        $request = $this->createMockRequest('put', 'application/json');

        $result = $this->filter->before($request);

        $this->assertNull($result, 'PUT requests should bypass content-type check');
    }

    public function testDeleteRequestBypassesContentTypeCheck(): void
    {
        $request = $this->createMockRequest('delete');

        $result = $this->filter->before($request);

        $this->assertNull($result, 'DELETE requests should bypass content-type check');
    }
}
