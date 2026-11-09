<?php

namespace Tests\Unit;

use App\Services\ValidationService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ValidationServiceTest extends CIUnitTestCase
{
    private ValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ValidationService();
    }

    public function testSanitizeSlugWithCleanSlug(): void
    {
        $this->assertSame('valid-slug', $this->service->sanitizeSlug('valid-slug'));
    }

    public function testSanitizeSlugWithAlphanumeric(): void
    {
        $this->assertSame('slug123', $this->service->sanitizeSlug('slug123'));
    }

    public function testSanitizeSlugWithUnderscores(): void
    {
        $this->assertSame('slug_name', $this->service->sanitizeSlug('slug_name'));
    }

    public function testSanitizeSlugRemovesSpecialCharacters(): void
    {
        $this->assertSame('validslug', $this->service->sanitizeSlug('valid@#$slug'));
    }

    public function testSanitizeSlugCollapsesMultipleHyphens(): void
    {
        $this->assertSame('a-b', $this->service->sanitizeSlug('a--b'));
        $this->assertSame('a-b', $this->service->sanitizeSlug('a---b'));
    }

    public function testSanitizeSlugWithEmptyString(): void
    {
        $this->assertSame('', $this->service->sanitizeSlug(''));
    }

    public function testSanitizeSlugPreservesCase(): void
    {
        $this->assertSame('MySlug', $this->service->sanitizeSlug('MySlug'));
    }

    public function testValidateGateKeyWithValidKey(): void
    {
        $this->assertTrue($this->service->validateGateKey('valid-key_123'));
    }

    public function testValidateGateKeyWithNull(): void
    {
        $this->assertFalse($this->service->validateGateKey(null));
    }

    public function testValidateGateKeyWithSpecialCharacters(): void
    {
        $this->assertFalse($this->service->validateGateKey('key with spaces'));
        $this->assertFalse($this->service->validateGateKey('key@special'));
    }

    public function testValidateGateKeyWithEmptyString(): void
    {
        $this->assertFalse($this->service->validateGateKey(''));
    }
}
