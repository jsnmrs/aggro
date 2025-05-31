<?php

namespace App\Filters;

use App\Libraries\SentryService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\Transaction;

class SentryPerformance implements FilterInterface
{
    protected ?Transaction $transaction = null;
    protected SentryService $sentry;

    public function __construct()
    {
        $this->sentry = new SentryService();
    }

    /**
     * Start performance monitoring transaction before the request
     *
     * @param mixed|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Only run in production and development environments
        if (! in_array(ENVIRONMENT, ['production', 'development'], true)) {
            return;
        }

        // Get the route
        $router     = service('router');
        $controller = $router->controllerName();
        $method     = $router->methodName();

        // Create transaction name
        $transactionName = $request->getMethod() . ' ';
        if ($controller && $method) {
            $transactionName .= $controller . '::' . $method;
        } else {
            $transactionName .= $request->getPath();
        }

        // Start the transaction
        $this->transaction = $this->sentry->startTransaction($transactionName, 'http.server');

        if ($this->transaction) {
            // Set additional transaction data
            $transactionData = [
                'url'    => current_url(),
                'method' => $request->getMethod(),
                'ip'     => $request->getIPAddress(),
            ];

            // Only add user agent for HTTP requests (not CLI requests)
            if (! is_cli() && method_exists($request, 'getUserAgent')) {
                $userAgent = $request->getUserAgent();
                if ($userAgent !== null) {
                    $transactionData['user_agent'] = $userAgent->getAgentString();
                }
            }

            $this->transaction->setData($transactionData);

            // Set the transaction as the current span
            SentrySdk::getCurrentHub()->setSpan($this->transaction);
        }
    }

    /**
     * Finish the performance monitoring transaction after the request
     *
     * @param mixed|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($this->transaction) {
            // Set HTTP status
            $this->transaction->setHttpStatus($response->getStatusCode());

            // Add response size if available
            if ($response->getBody()) {
                $this->transaction->setData([
                    'response_size' => strlen($response->getBody()),
                ]);
            }

            // Finish the transaction
            $this->transaction->finish();

            // Flush to ensure the transaction is sent
            $this->sentry->flush(2);
        }
    }
}
