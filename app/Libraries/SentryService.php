<?php

namespace App\Libraries;

use CodeIgniter\CodeIgniter;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Sentry as SentryConfig;
use Sentry;
use Sentry\Breadcrumb;
use Sentry\Event;
use Sentry\SentrySdk;
use Sentry\Severity;
use Sentry\State\Scope;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Throwable;

class SentryService
{
    protected SentryConfig $config;
    protected bool $initialized = false;

    public function __construct()
    {
        $this->config = config('Sentry');
        $this->initialize();
    }

    protected function initialize(): void
    {
        // Allow Sentry in production and development environments
        $allowedEnvironments = ['production', 'development'];

        if (empty($this->config->dsn) || $this->initialized || ! in_array(ENVIRONMENT, $allowedEnvironments, true)) {
            return;
        }

        Sentry\init([
            'dsn'                => $this->config->dsn,
            'environment'        => $this->config->environment,
            'release'            => $this->config->release,
            'sample_rate'        => $this->config->sampleRate,
            'traces_sample_rate' => $this->config->tracesSampleRate,
            'send_default_pii'   => $this->config->sendDefaultPii,
            'max_breadcrumbs'    => $this->config->maxBreadcrumbs,
            'attach_stacktrace'  => $this->config->attachStacktrace,
            'integrations'       => $this->config->integrations,
            'before_send'        => function (Event $event): ?Event {
                // Filter out sensitive data if needed
                return $this->filterSensitiveData($event);
            },
        ]);

        $this->initialized = true;
        $this->configureScope();
    }

    protected function configureScope(): void
    {
        Sentry\configureScope(static function (Scope $scope): void {
            // Add application context
            $scope->setContext('app', [
                'name'        => 'BMXFeed',
                'environment' => ENVIRONMENT,
                'base_url'    => base_url(),
                'ci_version'  => CodeIgniter::CI_VERSION,
                'php_version' => PHP_VERSION,
            ]);

            // Add user context if available
            // Check if session is already started to avoid ini_set warnings
            if (session_status() === PHP_SESSION_ACTIVE && session()->has('user_id')) {
                $scope->setUser([
                    'id'       => session('user_id'),
                    'username' => session('username') ?? null,
                ]);
            }

            // Add request context
            $request = service('request');
            if ($request) {
                $requestContext = [
                    'method' => $request->getMethod(),
                    'url'    => current_url(),
                    'ip'     => $request->getIPAddress(),
                ];

                // Only add user agent for HTTP requests (not CLI requests)
                // Check if getUserAgent method exists to avoid errors with CLIRequest
                if ($request instanceof IncomingRequest && method_exists($request, 'getUserAgent')) {
                    $requestContext['user_agent'] = $request->getUserAgent()->getAgentString();
                }

                $scope->setContext('request', $requestContext);
            }
        });
    }

    public function captureException(Throwable $exception, array $context = []): ?string
    {
        if (! $this->initialized || empty($this->config->dsn)) {
            return null;
        }

        // Add context if provided
        if (! empty($context)) {
            Sentry\withScope(static function (Scope $scope) use ($exception, $context): void {
                foreach ($context as $key => $value) {
                    // Ensure value is an array as required by Sentry
                    $contextValue = is_array($value) ? $value : ['value' => $value];
                    $scope->setContext($key, $contextValue);
                }
                Sentry\captureException($exception);
            });

            return SentrySdk::getCurrentHub()->getLastEventId();
        }

        return Sentry\captureException($exception);
    }

    public function captureMessage(string $message, string $level = 'info', array $context = []): ?string
    {
        if (! $this->initialized || empty($this->config->dsn)) {
            return null;
        }

        // Convert string level to Severity object
        $severityLevel = match ($level) {
            'debug'   => Severity::debug(),
            'info'    => Severity::info(),
            'warning' => Severity::warning(),
            'error'   => Severity::error(),
            'fatal'   => Severity::fatal(),
            default   => Severity::info(),
        };

        // Add context if provided
        if (! empty($context)) {
            Sentry\withScope(static function (Scope $scope) use ($message, $severityLevel, $context): void {
                foreach ($context as $key => $value) {
                    // Ensure value is an array as required by Sentry
                    $contextValue = is_array($value) ? $value : ['value' => $value];
                    $scope->setContext($key, $contextValue);
                }
                Sentry\captureMessage($message, $severityLevel);
            });

            return SentrySdk::getCurrentHub()->getLastEventId();
        }

        return Sentry\captureMessage($message, $severityLevel);
    }

    public function addBreadcrumb(string $message, string $category = 'custom', array $data = []): void
    {
        if (! $this->initialized || empty($this->config->dsn)) {
            return;
        }

        Sentry\addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            $category,
            $message,
            $data,
        ));
    }

    public function startTransaction(string $name, string $op = 'http.server'): ?Transaction
    {
        if (! $this->initialized || empty($this->config->dsn)) {
            return null;
        }

        $context = new TransactionContext();
        $context->setName($name);
        $context->setOp($op);

        return Sentry\startTransaction($context);
    }

    protected function filterSensitiveData(Event $event): ?Event
    {
        // Filter out disallowed character exceptions for random/invalid routes
        $exceptions = $event->getExceptions();
        if (! empty($exceptions)) {
            foreach ($exceptions as $exception) {
                if ($exception->getType() === 'CodeIgniter\HTTP\Exceptions\BadRequestException'
                    && str_contains($exception->getValue(), 'The URI you submitted has disallowed characters')) {
                    return null;
                }
            }
        }

        // Filter out sensitive data from the event
        $request = $event->getRequest();
        if ($request && is_array($request)) {
            // Remove sensitive headers
            if (isset($request['headers'])) {
                unset($request['headers']['authorization'], $request['headers']['cookie']);
            }

            // Remove sensitive data from request data
            if (isset($request['data']) && is_array($request['data'])) {
                $sensitiveKeys = ['password', 'passwd', 'pwd', 'secret', 'token', 'api_key', 'apikey'];

                foreach ($sensitiveKeys as $key) {
                    if (isset($request['data'][$key])) {
                        $request['data'][$key] = '[FILTERED]';
                    }
                }
            }

            // Update the request data
            $event->setRequest($request);
        }

        return $event;
    }

    public function flush(?int $timeout = null): void
    {
        if ($this->initialized) {
            Sentry\flush($timeout);
        }
    }
}
