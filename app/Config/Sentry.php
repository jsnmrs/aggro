<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

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
            $gitVersion = trim(shell_exec('git describe --tags --always 2>/dev/null'));
            $release    = ! empty($gitVersion) ? $gitVersion : date('Y-m-d-His');
        }
        $this->release = $release;

        $this->sampleRate       = (float) env('SENTRY_SAMPLE_RATE', $this->sampleRate);
        $this->tracesSampleRate = (float) env('SENTRY_TRACES_SAMPLE_RATE', $this->tracesSampleRate);
        $this->sendDefaultPii   = (bool) env('SENTRY_SEND_DEFAULT_PII', $this->sendDefaultPii);
    }
}
