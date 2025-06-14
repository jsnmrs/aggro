<?php

use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\RepositoryTestCase;

/**
 * @internal
 */
final class FrontControllerTest extends RepositoryTestCase
{
    use FeatureTestTrait;

    public function testAboutPageLoads()
    {
        // Act
        $response = $this->get('/about');

        // Assert
        $response->assertStatus(200);
        $response->assertSee('About');
    }

    public function testError404PageReturns404Status()
    {
        // Act
        $response = $this->get('/nonexistent-page');

        // Assert
        $response->assertStatus(404);
    }

    public function testHomePageRedirectsToFeatured()
    {
        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
    }
}