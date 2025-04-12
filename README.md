# Aggro

Aggro is the codebase that powers [BMXfeed](https://bmxfeed.com), a BMX news aggregator and video discovery platform. Running continuously since 2006, BMXfeed collects and curates BMX-related content from across the web.

![BMXfeed Screenshot](https://user-images.githubusercontent.com/1215760/98826155-4a15c100-242d-11eb-81fa-cdbe68a3e872.jpg)

## Features

- **News Aggregation**: Automatically collects and displays BMX news from various sources
- **Video Integration**: Aggregates BMX videos from YouTube and Vimeo
- **RSS Feed Directory**: Maintains a curated directory of BMX-related RSS feeds
- **Content Curation**: Automatically archives old content and manages content quality
- **API Support**: Integrates with YouTube and Vimeo APIs for video metadata
- **Feed Generation**: Provides RSS/OPML feeds of aggregated content
- **Responsive Design**: Mobile-first, responsive web interface

## Tech Stack

- **Backend**: PHP 8.2+ with CodeIgniter 4 framework
- **Frontend**: Vanilla CSS with PostCSS processing and no JavaScript!
- **Database**: MySQL/MariaDB
- **Dependencies**: 
  - SimplePie for feed parsing
  - Composer for PHP package management
  - npm for frontend build tooling

## Local Development Setup

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

### Development Commands

Aggro includes several custom Docksal commands to help with development:

- `fin admin` - Run application maintenance tasks
- `fin deploy [env]` - Deploy to specified environment
- `fin frontend` - Run frontend build process
- `fin maintain` - Run upgrades and tests
- `fin test` - Run test suite
- `fin upgrade` - Update Composer packages

## Configuration

### Environment Variables

Copy `.env-sample` to `.env` for your local environment. Key configurations:

- `CI_ENVIRONMENT` - Set to 'development' for local work
- `app.baseURL` - Your local URL (default: http://aggro.docksal.site)
- Database credentials (configured through Docksal)
- API keys for video services

### Cron Jobs

The `.crontab` file defines scheduled tasks for:

- News feed updates (every 6 minutes)
- YouTube video checks (every 5 minutes)
- Vimeo video checks (every 7 minutes)
- Archive management (daily)
- Feed cache clearing (monthly)

## Testing

The project includes several types of tests:

```bash
# Run all tests
fin test

# Run specific checks
fin sniff      # PHP CodeSniffer
fin shellcheck # Shell script linting
```

## Deployment

Deployment is handled through GitHub Actions and Deployer:

1. Automated deployment on merge to main branch
2. Frontend assets are built and included in deployment
3. Environment files are securely transferred
4. Crontab is updated on deployment

### Manual Deployment

```bash
# Deploy to development
fin deploy dev

# Deploy to production
fin deploy prod
```

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
- Email: jason@bmxfeed.com
