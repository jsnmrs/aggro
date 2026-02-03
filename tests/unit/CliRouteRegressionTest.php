<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests to prevent CLI route regressions.
 *
 * When HTTP method restrictions are added (e.g., changing $routes->add() to
 * $routes->get()), CLI routes need explicit $routes->cli() definitions.
 * This test ensures all cron jobs have corresponding CLI route definitions.
 *
 * @internal
 */
final class CliRouteRegressionTest extends CIUnitTestCase
{
    private string $crontabPath;
    private string $routesPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crontabPath = ROOTPATH . '.crontab';
        $this->routesPath  = APPPATH . 'Config/Routes.php';
    }

    /**
     * Extract route paths from crontab entries.
     *
     * @return list<string> Array of route paths (e.g., ['aggro/news', 'aggro/sweep'])
     */
    private function getCrontabRoutes(): array
    {
        $this->assertFileExists($this->crontabPath, 'Crontab file not found');

        $content = file_get_contents($this->crontabPath);
        $lines   = explode("\n", $content);
        $routes  = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Skip variable definitions and DreamHost block
            if (str_contains($line, '=') && ! str_contains($line, '$')) {
                continue;
            }

            // Match cron entries that invoke index.php with a route
            // Pattern: $PHP_PATH $INDEX_PATH controller method [optional args]
            // Routes in crontab are space-separated: "aggro news" becomes "aggro/news"
            if (preg_match('/\$INDEX_PATH\s+(\S+)\s+(\S+)/', $line, $matches)) {
                $routes[] = $matches[1] . '/' . $matches[2];
            }
        }

        return array_unique($routes);
    }

    /**
     * Extract CLI route definitions from Routes.php.
     *
     * @return list<string> Array of CLI route paths
     */
    private function getCliRouteDefinitions(): array
    {
        $this->assertFileExists($this->routesPath, 'Routes.php file not found');

        $content = file_get_contents($this->routesPath);
        $routes  = [];

        // Match $routes->cli('path', ...) patterns
        if (preg_match_all('/\$routes->cli\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            $routes = $matches[1];
        }

        return $routes;
    }

    /**
     * Test that all crontab routes have corresponding CLI route definitions.
     */
    public function testAllCrontabRoutesHaveCliDefinitions(): void
    {
        $crontabRoutes = $this->getCrontabRoutes();
        $cliRoutes     = $this->getCliRouteDefinitions();

        $this->assertNotEmpty($crontabRoutes, 'No routes found in crontab');
        $this->assertNotEmpty($cliRoutes, 'No CLI routes found in Routes.php');

        $missingRoutes = [];

        foreach ($crontabRoutes as $crontabRoute) {
            // Check if this route or a parameterized version exists
            $routeFound = false;

            foreach ($cliRoutes as $cliRoute) {
                // Direct match
                if ($cliRoute === $crontabRoute) {
                    $routeFound = true;

                    break;
                }

                // Check for parameterized route (e.g., aggro/vimeo matches aggro/vimeo/(:segment))
                $baseRoute = preg_replace('/\/\([^)]+\)$/', '', $cliRoute);
                if ($baseRoute === $crontabRoute) {
                    $routeFound = true;

                    break;
                }
            }

            if (! $routeFound) {
                $missingRoutes[] = $crontabRoute;
            }
        }

        $this->assertEmpty(
            $missingRoutes,
            sprintf(
                "Missing CLI route definitions in Routes.php for crontab entries:\n- %s\n\n"
                . 'Add $routes->cli() definitions for these routes to prevent CLI access failures.',
                implode("\n- ", $missingRoutes),
            ),
        );
    }

    /**
     * Test that Routes.php contains CLI route definitions.
     */
    public function testRoutesFileHasCliDefinitions(): void
    {
        $this->assertFileExists($this->routesPath);

        $content = file_get_contents($this->routesPath);

        $this->assertStringContainsString(
            '$routes->cli(',
            $content,
            'Routes.php should contain CLI route definitions',
        );
    }

    /**
     * Test crontab file is readable and has expected structure.
     */
    public function testCrontabFileStructure(): void
    {
        $this->assertFileExists($this->crontabPath);

        $content = file_get_contents($this->crontabPath);

        // Check for required variable definitions
        $this->assertStringContainsString('PHP_PATH=', $content, 'Crontab should define PHP_PATH');
        $this->assertStringContainsString('INDEX_PATH=', $content, 'Crontab should define INDEX_PATH');
    }
}
