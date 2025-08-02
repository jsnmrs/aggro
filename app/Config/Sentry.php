<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use InvalidArgumentException;
use RuntimeException;

class Sentry extends BaseConfig
{
    /**
     * Sentry DSN (Data Source Name)
     */
    public string $dsn = '';

    /**
     * Environment name
     */
    public string $environment = 'production';

    /**
     * Release version
     */
    public string $release = '';

    /**
     * Sample rate for error events (0.0 to 1.0)
     * 1.0 = 100% of errors are sent
     * 0.1 = 10% of errors are sent
     */
    public float $sampleRate = 1.0;

    /**
     * Sample rate for performance monitoring (0.0 to 1.0)
     * 0.1 = 10% of transactions are sent
     */
    public float $tracesSampleRate = 0.1;

    /**
     * Send default personally identifiable information
     */
    public bool $sendDefaultPii = false;

    /**
     * Additional integrations
     */
    public array $integrations = [];

    /**
     * Maximum breadcrumbs
     */
    public int $maxBreadcrumbs = 100;

    /**
     * Attach stack trace to messages
     */
    public bool $attachStacktrace = true;

    public function __construct()
    {
        parent::__construct();

        // Load from environment variables
        $this->dsn         = env('SENTRY_DSN', $this->dsn);
        $this->environment = env('SENTRY_ENVIRONMENT', ENVIRONMENT);

        // Use git describe for release version, fallback to env or timestamp
        $release = env('SENTRY_RELEASE', '');
        if (empty($release)) {
            $gitVersion = $this->getGitVersion();
            $release    = ! empty($gitVersion) ? $gitVersion : date('Y-m-d-His');
        }
        $this->release = $release;

        $this->sampleRate       = (float) env('SENTRY_SAMPLE_RATE', $this->sampleRate);
        $this->tracesSampleRate = (float) env('SENTRY_TRACES_SAMPLE_RATE', $this->tracesSampleRate);
        $this->sendDefaultPii   = (bool) env('SENTRY_SEND_DEFAULT_PII', $this->sendDefaultPii);
    }

    /**
     * Get git version safely without shell injection vulnerabilities.
     *
     * Uses multiple secure methods in order of preference:
     * 1. proc_open with explicit command array (most secure)
     * 2. Reading git files directly from filesystem
     * 3. Returns empty string if git is not available
     *
     * @return string Git version or empty string
     */
    private function getGitVersion(): string
    {
        // Method 1: Use proc_open for secure command execution
        if (function_exists('proc_open')) {
            $gitVersion = $this->getGitVersionWithProcOpen();
            if (! empty($gitVersion)) {
                return $gitVersion;
            }
        }

        // Method 2: Read git files directly from filesystem
        $gitVersion = $this->getGitVersionFromFiles();
        if (! empty($gitVersion)) {
            return $gitVersion;
        }

        // Method 3: Fallback - return empty string
        return '';
    }

    /**
     * Get git version using proc_open for secure execution.
     *
     * @return string Git version or empty string
     */
    private function getGitVersionWithProcOpen(): string
    {
        try {
            $descriptorspec = [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w'],  // stderr
            ];

            // Use command array to prevent injection
            $command = ['git', 'describe', '--tags', '--always'];
            $process = proc_open($command, $descriptorspec, $pipes, ROOTPATH);

            if (! is_resource($process)) {
                return '';
            }

            // Close stdin, read stdout
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $error  = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);

            // Return output only if command succeeded
            if ($exitCode === 0 && ! empty($output)) {
                return trim($output);
            }
        } catch (InvalidArgumentException|RuntimeException $e) {
            // Log error but don't fail - this is non-critical functionality
            log_message('warning', 'Failed to get git version via proc_open: ' . $e->getMessage());
        }

        return '';
    }

    /**
     * Get git version by reading git files directly.
     *
     * @return string Git version or empty string
     */
    private function getGitVersionFromFiles(): string
    {
        try {
            $gitDir = ROOTPATH . '.git';

            // Check if .git directory exists
            if (! is_dir($gitDir)) {
                return '';
            }

            // Try to read HEAD file
            $headFile = $gitDir . '/HEAD';
            if (! is_readable($headFile)) {
                return '';
            }

            $head = trim(file_get_contents($headFile));

            // If HEAD points to a ref, read the commit hash
            if (str_starts_with($head, 'ref: ')) {
                $refPath = substr($head, 5); // Remove 'ref: ' prefix
                $refFile = $gitDir . '/' . $refPath;

                if (is_readable($refFile)) {
                    $commitHash = trim(file_get_contents($refFile));

                    return substr($commitHash, 0, 7); // Short hash
                }
            } elseif (ctype_xdigit($head) && strlen($head) >= 7) {
                // HEAD contains commit hash directly
                return substr($head, 0, 7);
            }
        } catch (InvalidArgumentException|RuntimeException $e) {
            // Log error but don't fail - this is non-critical functionality
            log_message('warning', 'Failed to get git version from files: ' . $e->getMessage());
        }

        return '';
    }
}
