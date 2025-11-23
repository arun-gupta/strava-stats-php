# Strava Activity Analyzer (PHP)

A PHP application for analyzing and visualizing Strava activity statistics.

## Quick Start

The fastest way to get started:

```bash
git clone https://github.com/arun-gupta/strava-stats-php.git
cd strava-stats-php
./quickstart.sh
```

The script will:
- Check for required dependencies (PHP, Composer, npm)
- Install all dependencies
- Build front-end assets
- Create `.env` file and prompt you to configure it
- Wait for you to add Strava API credentials
- Start the development server on http://localhost:8080

**Note:** You'll need to:
1. Register your application at https://www.strava.com/settings/api
2. Add your `STRAVA_CLIENT_ID`, `STRAVA_CLIENT_SECRET`, and `STRAVA_REDIRECT_URI` to `.env`
3. The script will pause and wait for you to complete this configuration

For manual installation, production deployment, and detailed configuration options, see [docs/INSTALLATION.md](docs/INSTALLATION.md).

## Features

- 📊 Activity count and time distribution by sport type
- 🔥 Training heatmaps showing workout consistency and streaks
- 🏃 Running statistics with personal records
- 📈 Mileage and pace trends over time
- 📅 Customizable date ranges and unit preferences (metric/imperial)
- 🔒 Secure OAuth2 authentication with Strava

## Framework Decision

This project uses **Slim Framework 4** for the following reasons:
- Lightweight and focused on HTTP routing and middleware
- Minimal overhead, ideal for API-first applications
- PSR-7/PSR-15 compliant (HTTP messages and request handlers)
- Flexible architecture with explicit dependency injection
- Easy to understand and maintain for a single-purpose analytics app

Laravel was considered but deemed too heavy for this focused use case where we primarily need OAuth handling, API endpoints, and basic views.

## Documentation

- [Installation Guide](docs/INSTALLATION.md) - Manual installation, configuration, and production deployment
- [Requirements Document](docs/requirements.md) - Detailed project requirements
- [Development Plan](docs/plan.md) - Implementation roadmap
- [Task Checklist](docs/tasks.md) - Development task tracking

## License

Apache License 2.0 - See [LICENSE](LICENSE) for details.
