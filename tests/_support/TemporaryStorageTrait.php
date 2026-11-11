<?php

namespace Tests\Support;

/**
 * Points the storage paths at throwaway directories, so tests that
 * write or delete files never touch the application's own.
 */
trait TemporaryStorageTrait
{
    /**
     * Run a test against a temporary feed cache directory.
     *
     * @param callable(string): void $test Receives the directory path with a trailing slash
     */
    private function withFeedCacheDirectory(callable $test): void
    {
        $this->withStorageDirectory('feedCachePath', 'aggro-cache-', $test);
    }

    /**
     * Swap a Storage path for a temporary directory while the test runs.
     *
     * @param string                 $property Storage config property holding the path
     * @param string                 $prefix   Name prefix for the temporary directory
     * @param callable(string): void $test     Receives the directory path with a trailing slash
     */
    private function withStorageDirectory(string $property, string $prefix, callable $test): void
    {
        $storageConfig = config('Storage');
        $original      = $storageConfig->{$property};
        $directory     = sys_get_temp_dir() . '/' . $prefix . uniqid() . '/';
        mkdir($directory);

        $storageConfig->{$property} = $directory;

        try {
            $test($directory);
        } finally {
            $storageConfig->{$property} = $original;
            array_map('unlink', glob($directory . '*'));
            rmdir($directory);
        }
    }
}
