<?php

use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\RepositoryTestCase;

/**
 * Sends real POST requests through the framework, so the security
 * filter sees the request method the way the framework reports it.
 *
 * @internal
 */
final class PostContentTypeTest extends RepositoryTestCase
{
    use FeatureTestTrait;

    public function testPostWithUnsupportedContentTypeIsRejected()
    {
        $response = $this->withHeaders(['Content-Type' => 'text/plain'])
            ->post('aggro/news-clean');

        $response->assertStatus(415);
    }

    public function testPostWithFormContentTypeReachesTheController()
    {
        $response = $this->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->post('aggro/news-clean');

        $response->assertStatus(200);
        $response->assertSee('Featured news stories cleared.');
    }

    public function testPostWithoutContentTypeReachesTheController()
    {
        $response = $this->post('aggro/news-clean');

        $response->assertStatus(200);
        $response->assertSee('Featured news stories cleared.');
    }
}
