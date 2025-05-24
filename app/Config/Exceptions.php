<?php

namespace Config;

use App\Libraries\SentryService;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Setup how the exception handler works.
 */
class Exceptions extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * LOG EXCEPTIONS?
     * --------------------------------------------------------------------------
     * If true, then exceptions will be logged
     * through Services::Log.
     *
     * Default: true
     */
    public bool $log = true;

    /**
     * --------------------------------------------------------------------------
     * DO NOT LOG STATUS CODES
     * --------------------------------------------------------------------------
     * Any status codes here will NOT be logged if logging is turned on.
     * By default, only 404 (Page Not Found) exceptions are ignored.
     */
    public array $ignoreCodes = [404];

    /**
     * --------------------------------------------------------------------------
     * Error Views Path
     * --------------------------------------------------------------------------
     * This is the path to the directory that contains the 'cli' and 'html'
     * directories that hold the views used to generate errors.
     *
     * Default: APPPATH.'Views/errors'
     */
    public string $errorViewPath = APPPATH . 'Views/errors';

    /**
     * --------------------------------------------------------------------------
     * HIDE FROM DEBUG TRACE
     * --------------------------------------------------------------------------
     * Any data that you would like to hide from the debug trace.
     * In order to specify 2 levels, use "/" to separate.
     * ex. ['server', 'setup/password', 'secret_token']
     */
    public array $sensitiveDataInTrace = [];

    /**
     * --------------------------------------------------------------------------
     * LOG DEPRECATIONS INSTEAD OF THROWING?
     * --------------------------------------------------------------------------
     * By default, CodeIgniter converts deprecations into exceptions. Also,
     * starting in PHP 8.1 will cause a lot of deprecated usage warnings.
     * Use this option to temporarily cease the warnings and instead log those.
     * This option also works for user deprecations.
     */
    public bool $logDeprecations = true;

    /**
     * --------------------------------------------------------------------------
     * LOG LEVEL THRESHOLD FOR DEPRECATIONS
     * --------------------------------------------------------------------------
     * If `$logDeprecations` is set to `true`, this sets the log level
     * to which the deprecation will be logged. This should be one of the log
     * levels recognized by PSR-3.
     *
     * The related `Config\Logger::$threshold` should be adjusted, if needed,
     * to capture logging the deprecations.
     */
    public string $deprecationLogLevel = LogLevel::WARNING;

    /*
     * DEFINE THE HANDLERS USED
     * --------------------------------------------------------------------------
     * Given the HTTP status code, returns exception handler that
     * should be used to deal with this error. By default, it will run CodeIgniter's
     * default handler and display the error information in the expected format
     * for CLI, HTTP, or AJAX requests, as determined by is_cli() and the expected
     * response format.
     *
     * Custom handlers can be returned if you want to handle one or more specific
     * error codes yourself like:
     *
     *      if (in_array($statusCode, [400, 404, 500])) {
     *          return new \App\Libraries\MyExceptionHandler();
     *      }
     *      if ($exception instanceOf PageNotFoundException) {
     *          return new \App\Libraries\MyExceptionHandler();
     *      }
     */
    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        return new class ($this, $statusCode, $exception) implements ExceptionHandlerInterface {
            private $config;
            private int $statusCode;
            private Throwable $exception;
            private ExceptionHandler $defaultHandler;

            public function __construct($config, int $statusCode, Throwable $exception)
            {
                $this->config = $config;
                $this->statusCode = $statusCode;
                $this->exception = $exception;
                $this->defaultHandler = new ExceptionHandler($config);
            }

            public function handle(
                Throwable $exception,
                \CodeIgniter\HTTP\RequestInterface $request,
                \CodeIgniter\HTTP\ResponseInterface $response,
                int $statusCode,
                int $exitCode
            ): void {
                // Send to Sentry in production if not an ignored code
                if (ENVIRONMENT === 'production' && !in_array($statusCode, $this->config->ignoreCodes)) {
                    try {
                        $sentry = new SentryService();
                        $sentry->captureException($exception, [
                            'http' => [
                                'status_code' => $statusCode,
                                'method' => $request->getMethod(),
                                'url' => current_url(),
                            ],
                        ]);
                    } catch (\Throwable $e) {
                        // Silently fail if Sentry is not working
                        // We don't want Sentry errors to break the app
                        log_message('error', 'Sentry failed to capture exception: ' . $e->getMessage());
                    }
                }

                // Continue with default handling
                $this->defaultHandler->handle($exception, $request, $response, $statusCode, $exitCode);
            }
        };
    }
}
