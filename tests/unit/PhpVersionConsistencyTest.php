<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests to ensure PHP version consistency across configuration files.
 *
 * PHP version must match across:
 * - .crontab (production PHP path)
 * - .github/workflows/deploy.yml (CI/CD)
 * - .docksal/docksal.env (local development)
 * - composer.json (dependency requirements)
 *
 * @internal
 */
final class PhpVersionConsistencyTest extends CIUnitTestCase
{
    /**
     * Extract PHP version from crontab.
     *
     * Expects format like: PHP_PATH='/usr/local/php84/bin/php'
     */
    private function getCrontabPhpVersion(): ?string
    {
        $path = ROOTPATH . '.crontab';
        if (! file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        // Match php84, php83, etc. in the path
        if (preg_match('/PHP_PATH.*php(\\d+)/', $content, $matches)) {
            $version = $matches[1];

            // Convert 84 to 8.4
            return substr($version, 0, 1) . '.' . substr($version, 1);
        }

        return null;
    }

    /**
     * Extract PHP version from deploy.yml.
     *
     * Expects format like: php-version: '8.4'
     */
    private function getDeployPhpVersion(): ?string
    {
        $path = ROOTPATH . '.github/workflows/deploy.yml';
        if (! file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if (preg_match("/php-version:\\s*['\"]?(\\d+\\.\\d+)/", $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract PHP version from docksal.env.
     *
     * Expects format like: CLI_IMAGE='docksal/cli:php8.4-edge'
     */
    private function getDocksalPhpVersion(): ?string
    {
        $path = ROOTPATH . '.docksal/docksal.env';
        if (! file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if (preg_match('/php(\\d+\\.\\d+)/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract PHP version from composer.json.
     *
     * Expects format like: "php": "^8.4"
     */
    private function getComposerPhpVersion(): ?string
    {
        $path = ROOTPATH . 'composer.json';
        if (! file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        $json    = json_decode($content, true);

        if (isset($json['require']['php'])) {
            $constraint = $json['require']['php'];
            // Extract version from constraints like ^8.4, >=8.4, 8.4.*
            if (preg_match('/(\d+\.\d+)/', $constraint, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Test that PHP version is consistent across all configuration files.
     */
    public function testPhpVersionConsistency(): void
    {
        $versions = [
            '.crontab'                     => $this->getCrontabPhpVersion(),
            '.github/workflows/deploy.yml' => $this->getDeployPhpVersion(),
            '.docksal/docksal.env'         => $this->getDocksalPhpVersion(),
            'composer.json'                => $this->getComposerPhpVersion(),
        ];

        // Filter out null values (missing files)
        $foundVersions = array_filter($versions, static fn ($v) => $v !== null);

        $this->assertNotEmpty($foundVersions, 'No PHP version found in any configuration file');

        $uniqueVersions = array_unique($foundVersions);

        if (count($uniqueVersions) > 1) {
            $message = "PHP version mismatch detected:\n";

            foreach ($foundVersions as $file => $version) {
                $message .= sprintf("  - %s: %s\n", $file, $version);
            }
            $message .= "\nAll configuration files should specify the same PHP version.";

            $this->fail($message);
        }

        $this->assertCount(1, $uniqueVersions, 'PHP versions should be consistent');
    }

    /**
     * Test that required configuration files exist.
     */
    public function testRequiredConfigFilesExist(): void
    {
        $requiredFiles = [
            '.crontab'                     => ROOTPATH . '.crontab',
            '.github/workflows/deploy.yml' => ROOTPATH . '.github/workflows/deploy.yml',
            '.docksal/docksal.env'         => ROOTPATH . '.docksal/docksal.env',
            'composer.json'                => ROOTPATH . 'composer.json',
        ];

        foreach ($requiredFiles as $name => $path) {
            $this->assertFileExists($path, sprintf('%s should exist', $name));
        }
    }

    /**
     * Test that PHP version can be extracted from each file.
     */
    public function testPhpVersionExtractable(): void
    {
        $this->assertNotNull(
            $this->getCrontabPhpVersion(),
            'Could not extract PHP version from .crontab',
        );

        $this->assertNotNull(
            $this->getDeployPhpVersion(),
            'Could not extract PHP version from deploy.yml',
        );

        $this->assertNotNull(
            $this->getDocksalPhpVersion(),
            'Could not extract PHP version from docksal.env',
        );

        $this->assertNotNull(
            $this->getComposerPhpVersion(),
            'Could not extract PHP version from composer.json',
        );
    }
}
