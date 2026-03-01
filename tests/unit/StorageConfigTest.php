<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Storage;

/**
 * @internal
 */
final class StorageConfigTest extends CIUnitTestCase
{
    private Storage $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Storage();
    }

    public function testGetThumbnailPathReturnsCorrectPath(): void
    {
        $path = $this->config->getThumbnailPath('abc123');

        $this->assertStringEndsWith('abc123.webp', $path);
        $this->assertStringContainsString('thumbs/', $path);
    }

    public function testGetThumbnailGlobReturnsPattern(): void
    {
        $glob = $this->config->getThumbnailGlob();

        $this->assertStringEndsWith('*.webp', $glob);
        $this->assertStringContainsString('thumbs/', $glob);
    }

    public function testGetCleanupAgeSeconds(): void
    {
        $seconds = $this->config->getCleanupAgeSeconds();

        $this->assertSame(60 * 60 * 24 * $this->config->cleanupDays, $seconds);
        $this->assertGreaterThan(0, $seconds);
    }

    public function testGetArchiveAgeSeconds(): void
    {
        $seconds = $this->config->getArchiveAgeSeconds();

        $this->assertSame(60 * 60 * 24 * $this->config->archiveDays, $seconds);
        $this->assertGreaterThan(0, $seconds);
    }

    public function testDefaultValues(): void
    {
        $this->assertSame('.webp', $this->config->thumbnailExtension);
        $this->assertSame(600, $this->config->thumbnailWidth);
        $this->assertSame(338, $this->config->thumbnailHeight);
        $this->assertSame(40, $this->config->thumbnailQuality);
        $this->assertSame(31, $this->config->archiveDays);
        $this->assertSame(45, $this->config->cleanupDays);
        $this->assertSame(61, $this->config->minVideoDuration);
        $this->assertSame(1800, $this->config->defaultCacheDuration);
        $this->assertSame(10, $this->config->feedItemLimit);
        $this->assertSame(20, $this->config->feedTimeout);
        $this->assertSame(20, $this->config->urlConnectTimeout);
        $this->assertSame(4, $this->config->urlMaxRedirects);
    }

    public function testArchiveAgeIsLessThanCleanupAge(): void
    {
        // Archive age should be less than cleanup age (archive before cleanup)
        $this->assertLessThan(
            $this->config->getCleanupAgeSeconds(),
            $this->config->getArchiveAgeSeconds(),
        );
    }

    public function testThumbnailPathIncludesExtension(): void
    {
        $path = $this->config->getThumbnailPath('test_id');

        $this->assertStringEndsWith($this->config->thumbnailExtension, $path);
    }
}
