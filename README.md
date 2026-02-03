# Aggro

Aggro is the codebase that powers [BMXfeed](https://bmxfeed.com), a BMX news aggregator and video discovery platform. Running continuously since 2006, BMXfeed collects and curates BMX-related content from across the web.

![BMXfeed Screenshot](https://user-images.githubusercontent.com/1215760/98826155-4a15c100-242d-11eb-81fa-cdbe68a3e872.jpg)

## Features

- News aggregation — automatically collects and displays BMX news from various sources
- Video integration — aggregates BMX videos from YouTube and Vimeo
- RSS feed directory — maintains a curated directory of BMX-related RSS feeds
- Content curation — automatically archives old content and manages content quality
- API support — integrates with YouTube and Vimeo APIs for video metadata
- Feed generation — provides RSS/OPML feeds of aggregated content
- Responsive design — mobile-first, responsive web interface
- Error monitoring — integrated Sentry for real-time error tracking and performance monitoring
- Enhanced security — parameterized database queries, secure configuration management, comprehensive input validation, CSRF protection, security headers

## Tech stack

- Back-end — PHP 8.4+ with CodeIgniter 4 framework
- Front-end — vanilla CSS with PostCSS processing and no JavaScript!
- Database — MySQL/MariaDB (SQLite for testing)
- Testing — PHPUnit 11.5 for comprehensive unit testing with coverage reporting
- Debugging — Xdebug enabled for local development
- Error monitoring — Sentry for application monitoring and error tracking
- Code quality — PHP CS Fixer, PHP CodeSniffer, PHPMD, PHPStan for static analysis and code standards
- Dependencies — SimplePie for feed parsing, Composer for PHP package management, npm for front-end build tooling

## Architecture

Aggro follows a clean architecture pattern with separation of concerns:

- Controllers — Handle HTTP requests and coordinate responses
- Models — Core business logic and data structures
- Repositories — Data access layer for database operations
- Services — Domain-specific business logic (archiving, thumbnails)
- Helpers — Utility functions for common operations
- Libraries — Third-party integrations and custom components

This architecture improves code maintainability, testability, and follows SOLID principles. The clean separation of concerns enables comprehensive unit testing with 372 tests achieving 46.22% line coverage across all architectural layers.

## Local development setup

Aggro uses [Docksal](https://docksal.io) for local development. This ensures a consistent development environment across machines.

### Prerequisites

1. Install [Docksal](https://docksal.io/installation)
2. Docker compatible host system

### Installation

1. Clone the repository and enter directory:
   ```bash
   git clone https://github.com/jsnmrs/aggro.git
   cd aggro
   ```

2. Initialize the project:
   ```bash
   fin init
   ```

3. View the site:
   - Open http://aggro.docksal.site in your browser
   - The init process creates a local database from aggro-db.sql

### Debugging with Xdebug

Xdebug is enabled by default in the Docksal environment for debugging and profiling:

- Server name — `aggro.docksal.site`
- Port — `9003`
- IDE key — `VSCODE`
- Coverage — Enabled for test coverage reports

Configure VS Code to listen for Xdebug connections on port 9003. The debugger will automatically connect when triggered.

Performance note — Xdebug may slow down the application. If you experience performance issues during development, you can disable it by commenting out the xdebug configuration in `.docksal/etc/php/php.ini`.

### Development commands

Aggro includes several custom Docksal commands to help with development:

- `fin clicheck` — Run application maintenance tasks
- `fin deploy [env]` — Deploy to specified environment
- `fin frontend` — Run front-end build process
- `fin maintain` — Run upgrades and tests
- `fin test` — Run test suite
- `fin upgrade` — Update Composer packages

### CLI maintenance commands

Maintenance tasks can be run via CLI for cron jobs or automation:

- `php spark aggro/log-clean` — Clean old log entries
- `php spark aggro/log-error-clean` — Clean old error log entries
- `php spark aggro/news-cache` — Clear news feed cache
- `php spark aggro/news-clean` — Archive old news items
- `php spark aggro/sweep` — Run all maintenance tasks

## Configuration

### Environment variables

Copy `.env-sample` to `.env` for your local environment. Key configurations:

- `CI_ENVIRONMENT` — set to “development” for local work
- `app.baseURL` — your local URL (default: http://aggro.docksal.site)
- Database credentials — configured through Docksal
- API keys — for video services

### Sentry configuration

- `SENTRY_DSN` — your Sentry Data Source Name for error tracking
- `SENTRY_ENVIRONMENT` — environment name (development/production)
- `SENTRY_RELEASE` — application release version
- `SENTRY_SAMPLE_RATE` — error sampling rate (0.0 to 1.0)
- `SENTRY_TRACES_SAMPLE_RATE` — performance monitoring sample rate
- `SENTRY_SEND_DEFAULT_PII` — whether to send personally identifiable information

### Storage configuration

The `app/Config/Storage.php` file centralizes all file paths and storage-related settings:

- Thumbnail storage — path, dimensions, and quality settings
- Archive periods — content archival and cleanup timeframes
- Cache durations — default cache times for various operations
- Network timeouts — connection and request timeout settings

### Cron jobs

The `.crontab` file defines scheduled tasks for:

- News feed updates — every 6 minutes
- YouTube video checks — every 5 minutes
- Vimeo video checks — every 7 minutes
- Archive management — daily
- Feed cache clearing — monthly

## Testing

The project includes comprehensive testing infrastructure with 393 tests achieving 46.22% line coverage using PHPUnit for unit testing and multiple code quality tools.

### Test Suite Overview

- Total Tests — 393 comprehensive unit tests
- Coverage — 46.22% line coverage across all components
- Assertions — 471 test assertions ensuring thorough validation
- External Dependencies — 85 tests appropriately skipped for external services (YouTube/Vimeo APIs, Sentry, file system)
- Test Files — 74 test files covering all major components

### Unit Testing with PHPUnit

The test suite includes comprehensive coverage of:

- Controllers — HTTP request handling, response coordination, and validation (Feed: 100%, Home: 100%, BaseController: 100%)
- Models — Core business logic and data structures (AggroModels: 100%, NewsModels: 37.59%, UtilityModels: 55.88%)
- Helpers — Utility functions and common operations with comprehensive parameter validation
- Services — Domain-specific business logic (ArchiveService: 96.30%, ThumbnailService: 68.63%, ValidationService: 100%)
- Repositories — Data access layer operations (ChannelRepository: 100%, VideoRepository: 82.47%)
- Libraries — Third-party integrations (SentryService: 12.84%, SentryLogHandler: 19.44%)
- Filters — Request/response filtering (SecurityFilter: 100%, CustomCSRF: 100%, SentryPerformance: 12.90%)
- Security — Comprehensive security tests for SQL injection prevention, input validation, CSRF protection

Tests use an in-memory SQLite database for fast, isolated testing without affecting your development database. External services are properly mocked or skipped to ensure reliable test execution.

### Running Tests

```bash
# Run all tests (includes PHPUnit + linting)
fin test

# Run only PHPUnit unit tests
composer test:unit

# Run tests with coverage report (requires Xdebug)
XDEBUG_MODE=coverage composer test:coverage

# Run all Composer test scripts
composer test
```

### Test Coverage Reports

When running tests with coverage, detailed HTML reports are generated in:

- Coverage reports — `build/logs/html/index.html`
- Raw coverage data — `build/logs/coverage.xml`
- Current baseline — 46.22% line coverage, 42.52% method coverage

Open the HTML report in your browser to view detailed coverage metrics by file and function.

Note — Coverage reporting requires Xdebug to be enabled. Use `XDEBUG_MODE=coverage` when running coverage commands.

### Code Quality Checks

```bash
# Run specific checks
fin composer lint # PHP linting, static analysis
fin composer test # PHP unit tests
fin shellcheck # Shell script linting

# Individual quality tools
fin phpfix     # Auto-fix PHP code style issues
fin phpstan    # Run PHPStan static analysis
```

### Testing Best Practices

The test suite follows TDD principles and best practices:

- Proper Isolation — External dependencies (APIs, file system, network) are mocked or skipped
- Fast Execution — In-memory SQLite database for rapid test runs
- Comprehensive Coverage — Tests cover success paths, error conditions, and edge cases
- Clean Architecture — Testable design with dependency injection
- External Service Handling — 85 tests appropriately skipped for YouTube/Vimeo APIs, Sentry, and file operations

### Continuous Integration

GitHub Actions automatically runs the full test suite (393 tests) on all pull requests, including:

- PHPUnit unit tests with comprehensive coverage validation
- Code style checks (PHP CS Fixer, CodeSniffer)
- Static analysis (PHPStan, PHPMD)
- Shell script linting

All tests must pass before code can be merged, ensuring code quality and preventing regressions.

### Security Features

The application includes comprehensive security measures:

- CSRF Protection — Custom CSRF filter for all state-changing operations
- Input Validation — ValidationService provides sanitization for slugs, video IDs, gate keys, and integers
- SQL Injection Prevention — All database queries use parameterized statements
- Security Headers — Automatic security headers via SecurityFilter
- Null Byte Protection — Automatic null byte removal from all input
- Timing-Safe Comparisons — Used for sensitive string comparisons like gate keys

## Deployment

Deployment is handled through GitHub Actions and Deployer with automated testing:

1. Pull Request Testing — All PRs automatically run the complete test suite (393 tests) including PHPUnit tests, code quality checks, and static analysis
2. Automated deployment — Deployment to production occurs on merge to main branch (only after all tests pass)
3. Front-end assets — Assets are built and included in deployment
4. Environment files — Securely transferred during deployment
5. Crontab updates — Scheduled tasks are updated on deployment

The CI/CD pipeline ensures code quality by requiring all tests to pass before any code reaches production.

### Manual deployment

```bash
# Deploy to development
fin deploy dev

# Deploy to production
fin deploy prod
```

## Error monitoring

Aggro uses Sentry for application monitoring:

- Real-time error tracking and alerting
- Performance monitoring for slow requests
- Automatic error grouping and deduplication
- Integration with deployment tracking

Configure Sentry by setting the appropriate environment variables in your `.env` file.

## License

Aggro is open-source software licensed under the MIT license. See the [LICENSE](LICENSE) file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests (`fin test`)
5. Submit a pull request

## Credits

- Developed and maintained by [Jason Morris](https://jasonmorris.com)
- Built with [CodeIgniter 4](https://github.com/codeigniter4/CodeIgniter4)
- Uses [SimplePie](https://github.com/simplepie/simplepie) for feed parsing
- [Docksal](https://docksal.io) for development environment

## Support

- Issues — [GitHub Issues](https://github.com/jsnmrs/aggro/issues)
