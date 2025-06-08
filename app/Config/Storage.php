<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Storage Configuration
 *
 * Centralized configuration for file paths and storage-related constants.
 */
class Storage extends BaseConfig
{
    /**
     * Thumbnail storage configuration.
     */
    public string $thumbnailPath = ROOTPATH . 'public/thumbs/';

    public string $thumbnailExtension = '.webp';
    public int $thumbnailWidth        = 600;
    public int $thumbnailHeight       = 338;
    public int $thumbnailQuality      = 40;

    /**
     * Archive and cleanup time periods (in days).
     */
    public int $archiveDays = 31;

    public int $cleanupDays      = 45;
    public int $minVideoDuration = 61; // seconds

    /**
     * Cache and network configuration.
     */
    public int $defaultCacheDuration = 1800; // 30 minutes

    public int $feedItemLimit     = 10;
    public int $feedTimeout       = 20; // seconds
    public int $urlConnectTimeout = 20; // seconds
    public int $urlMaxRedirects   = 4;

    /**
     * Get full thumbnail path for a video.
     */
    public function getThumbnailPath(string $videoId): string
    {
        return $this->thumbnailPath . $videoId . $this->thumbnailExtension;
    }

    /**
     * Get thumbnail directory glob pattern.
     */
    public function getThumbnailGlob(): string
    {
        return $this->thumbnailPath . '*' . $this->thumbnailExtension;
    }

    /**
     * Get cleanup age in seconds.
     */
    public function getCleanupAgeSeconds(): int
    {
        return 60 * 60 * 24 * $this->cleanupDays;
    }

    /**
     * Get archive age in seconds.
     */
    public function getArchiveAgeSeconds(): int
    {
        return 60 * 60 * 24 * $this->archiveDays;
    }
}
