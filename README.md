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
- Enhanced security — parameterized database queries, secure configuration management, input validation

## Tech stack

- Back-end — PHP 8.2+ with CodeIgniter 4 framework
- Front-end — vanilla CSS with PostCSS processing and no JavaScript!
- Database — MySQL/MariaDB
- Error monitoring — Sentry for application monitoring and error tracking
- Code quality — PHP CS Fixer, PHP CodeSniffer, PHPMD, PHPStan for static analysis and code standards
- Dependencies — SimplePie for feed parsing, Composer for PHP package management, npm for front-end build tooling

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

### Development commands

Aggro includes several custom Docksal commands to help with development:

- `fin admin` — Run application maintenance tasks
- `fin deploy [env]` — Deploy to specified environment
- `fin frontend` — Run front-end build process
- `fin maintain` — Run upgrades and tests
- `fin test` — Run test suite
- `fin upgrade` — Update Composer packages

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

The project includes several types of tests:

```bash
# Run all tests
fin test

# Run all linting and static analysis
fin lint

# Run specific checks
fin sniff      # PHP CodeSniffer
fin shellcheck # Shell script linting

# Code quality checks
fin lint       # Run all linting (phpfix, phpcs, phpmd, phpstan)
fin phpfix     # Auto-fix PHP code style issues
fin phpstan    # Run PHPStan static analysis
```

## Deployment

Deployment is handled through GitHub Actions and Deployer:

1. Automated deployment on merge to main branch
2. Front-end assets are built and included in deployment
3. Environment files are securely transferred
4. Crontab is updated on deployment

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

- Issues: [GitHub Issues](https://github.com/jsnmrs/aggro/issues)
