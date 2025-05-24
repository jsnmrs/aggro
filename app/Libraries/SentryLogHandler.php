<?php

namespace App\Libraries;

use CodeIgniter\Log\Handlers\HandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

class SentryLogHandler implements HandlerInterface
{
    protected SentryService $sentry;
    protected array $handles = ['critical', 'alert', 'emergency', 'error'];
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->sentry = new SentryService();
    }

    /**
     * Checks whether this Handler will handle the given log level
     */
    public function canHandle(string $level): bool
    {
        return in_array($level, $this->handles, true);
    }

    /**
     * Handles the actual logging of the message
     *
     * @param mixed $level
     * @param mixed $message
     */
    public function handle($level, $message): bool
    {
        // Allow Sentry in development mode for testing
        // TODO: Remove development from this check before production deployment
        $allowedEnvironments = ['production', 'development'];
        if (! in_array(ENVIRONMENT, $allowedEnvironments, true)) {
            return true;
        }

        try {
            // Map log levels to Sentry severity levels
            $sentryLevel = $this->mapLogLevelToSentry($level);

            // Extract context if the message is an array or object
            $context = [];
            if (is_array($message) || is_object($message)) {
                $context['data'] = $message;
                $message         = json_encode($message);
            }

            // Check if the message contains an exception trace
            if (is_string($message) && str_starts_with($message, 'ERROR - ')) {
                // Try to extract meaningful information from error messages
                $context['raw_message'] = $message;

                // Clean up the message for better readability in Sentry
                $message = $this->cleanErrorMessage($message);
            }

            // Send to Sentry
            $this->sentry->captureMessage($message, $sentryLevel, $context);

            return true;
        } catch (Throwable $e) {
            // Don't let Sentry errors break the logging
            // This is already logged in the file handler
            return true;
        }
    }

    /**
     * Maps PSR-3 log levels to Sentry severity levels
     */
    protected function mapLogLevelToSentry(string $level): string
    {
        $map = [
            LogLevel::EMERGENCY => 'fatal',
            LogLevel::ALERT     => 'fatal',
            LogLevel::CRITICAL  => 'error',
            LogLevel::ERROR     => 'error',
            LogLevel::WARNING   => 'warning',
            LogLevel::NOTICE    => 'info',
            LogLevel::INFO      => 'info',
            LogLevel::DEBUG     => 'debug',
        ];

        return $map[$level] ?? 'info';
    }

    /**
     * Cleans up error messages for better readability
     */
    protected function cleanErrorMessage(string $message): string
    {
        // Remove timestamp and log level prefix
        $message = preg_replace('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} --> ERROR - /', '', $message);

        // Extract just the error message if it's a full stack trace
        if (preg_match('/^(.+?)\s+in\s+/', $message, $matches)) {
            return trim($matches[1]);
        }

        return trim($message);
    }

    /**
     * Handles batch logging
     */
    public function setDateFormat(string $format): HandlerInterface
    {
        return $this;
    }
}
