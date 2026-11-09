<?php

namespace Tests\Unit;

use App\Filters\SecurityFilter;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

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
        $request     = $this->createMockRequest('POST', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'multipart/form-data with boundary should be accepted');
    }

    public function testPostWithMultipartFormDataWithoutBoundaryIsAccepted(): void
    {
        $contentType = 'multipart/form-data';
        $request     = $this->createMockRequest('POST', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'multipart/form-data without boundary should be accepted');
    }

    public function testPostWithApplicationJsonIsAccepted(): void
    {
        $contentType = 'application/json';
        $request     = $this->createMockRequest('POST', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'application/json should be accepted');
    }

    public function testPostWithApplicationJsonCharsetIsAccepted(): void
    {
        $contentType = 'application/json; charset=utf-8';
        $request     = $this->createMockRequest('POST', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'application/json with charset should be accepted');
    }

    public function testPostWithFormUrlEncodedIsAccepted(): void
    {
        $contentType = 'application/x-www-form-urlencoded';
        $request     = $this->createMockRequest('POST', $contentType);

        $result = $this->filter->before($request);

        $this->assertNull($result, 'application/x-www-form-urlencoded should be accepted');
    }

    public function testPostWithInvalidContentTypeIsRejected(): void
    {
        $contentType = 'text/plain';
        $request     = $this->createMockRequest('POST', $contentType);

        $result = $this->filter->before($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(415, $result->getStatusCode());
    }

    public function testPostMethodIsMatchedInAnyCase(): void
    {
        $request = $this->createMockRequest('post', 'text/plain');

        $result = $this->filter->before($request);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(415, $result->getStatusCode());
    }

    public function testPostWithoutContentTypeIsAccepted(): void
    {
        // A POST with no Content-Type has no body for PHP to parse
        $request = $this->createMockRequest('POST');

        $result = $this->filter->before($request);

        $this->assertNull($result, 'A POST without a content type should be accepted');
    }

    public function testGetRequestBypassesContentTypeCheck(): void
    {
        $request = $this->createMockRequest('GET');

        $result = $this->filter->before($request);

        $this->assertNull($result, 'GET requests should bypass content-type check');
    }

    public function testPutRequestBypassesContentTypeCheck(): void
    {
        $request = $this->createMockRequest('PUT', 'application/json');

        $result = $this->filter->before($request);

        $this->assertNull($result, 'PUT requests should bypass content-type check');
    }

    public function testDeleteRequestBypassesContentTypeCheck(): void
    {
        $request = $this->createMockRequest('DELETE');

        $result = $this->filter->before($request);

        $this->assertNull($result, 'DELETE requests should bypass content-type check');
    }

    public function testAfterSetsXContentTypeOptionsHeader(): void
    {
        $request  = $this->createMockRequest('GET');
        $response = new Response(new App());

        $this->filter->after($request, $response);

        $this->assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
    }

    public function testAfterSetsXXSSProtectionHeader(): void
    {
        $request  = $this->createMockRequest('GET');
        $response = new Response(new App());

        $this->filter->after($request, $response);

        $this->assertSame('1; mode=block', $response->getHeaderLine('X-XSS-Protection'));
    }

    public function testAfterReturnsNull(): void
    {
        $request  = $this->createMockRequest('GET');
        $response = new Response(new App());

        $result = $this->filter->after($request, $response);

        $this->assertNull($result);
    }
}
