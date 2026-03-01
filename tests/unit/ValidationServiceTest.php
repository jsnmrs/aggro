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

    public function testValidateVideoIdWithValidYoutubeId(): void
    {
        // YouTube IDs are exactly 11 characters
        $this->assertSame('dQw4w9WgXcQ', $this->service->validateVideoId('dQw4w9WgXcQ', 'youtube'));
    }

    public function testValidateVideoIdWithValidYoutubeIdContainingHyphen(): void
    {
        $this->assertSame('abc-def_123', $this->service->validateVideoId('abc-def_123', 'youtube'));
    }

    public function testValidateVideoIdWithInvalidYoutubeIdTooShort(): void
    {
        $this->assertNull($this->service->validateVideoId('short', 'youtube'));
    }

    public function testValidateVideoIdWithInvalidYoutubeIdTooLong(): void
    {
        $this->assertNull($this->service->validateVideoId('wayTooLongVideoId', 'youtube'));
    }

    public function testValidateVideoIdWithValidVimeoId(): void
    {
        $this->assertSame('12345678', $this->service->validateVideoId('12345678', 'vimeo'));
    }

    public function testValidateVideoIdWithInvalidVimeoIdNonNumeric(): void
    {
        $this->assertNull($this->service->validateVideoId('abc12345', 'vimeo'));
    }

    public function testValidateVideoIdWithUnknownPlatform(): void
    {
        $this->assertNull($this->service->validateVideoId('12345', 'dailymotion'));
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

    public function testSanitizeIntWithValidValue(): void
    {
        $this->assertSame(5, $this->service->sanitizeInt(5));
    }

    public function testSanitizeIntWithStringValue(): void
    {
        $this->assertSame(42, $this->service->sanitizeInt('42'));
    }

    public function testSanitizeIntBelowMin(): void
    {
        $this->assertSame(0, $this->service->sanitizeInt(-5));
        $this->assertSame(10, $this->service->sanitizeInt(3, 10));
    }

    public function testSanitizeIntAboveMax(): void
    {
        $this->assertSame(100, $this->service->sanitizeInt(200, 0, 100));
    }

    public function testSanitizeIntWithNonNumeric(): void
    {
        $this->assertSame(0, $this->service->sanitizeInt('abc'));
        $this->assertSame(5, $this->service->sanitizeInt('abc', 5));
    }

    public function testSanitizeIntWithNull(): void
    {
        $this->assertSame(0, $this->service->sanitizeInt(null));
    }

    public function testSanitizeIntWithinRange(): void
    {
        $this->assertSame(50, $this->service->sanitizeInt(50, 0, 100));
    }

    public function testSanitizeIntAtBoundaries(): void
    {
        $this->assertSame(0, $this->service->sanitizeInt(0, 0, 100));
        $this->assertSame(100, $this->service->sanitizeInt(100, 0, 100));
    }
}
