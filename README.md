# Strava Activity Analyzer

Analyze and visualize your Strava activity statistics with beautiful charts and insights.

## Features

**Current:**
- 🔒 Secure OAuth2 authentication with Strava
- 📊 Activity distribution pie chart (last 7 days)
- 📈 Activity count breakdown by sport type
- 🎨 Interactive charts with tooltips and data labels
- ⚡ Loading states and error handling

**Coming Soon:**
- ⏱️ Duration analysis and time spent per activity type
- 🔥 Training heatmaps with consistency and streak tracking
- 🏃 Running statistics and personal records
- 📈 Mileage and pace trends over time
- 📅 Flexible date ranges (7d, 30d, 90d, Custom)
- ⚖️ Toggle between metric and imperial units

## Quick Start

```bash
git clone https://github.com/arun-gupta/strava-stats-php.git
cd strava-stats-php
./quickstart.sh
```

**Before the server starts**, you'll be prompted to:
1. Register your app at https://www.strava.com/settings/api
2. Edit `.env` and add your Strava API credentials

Then access the app at http://localhost:8080

> **Need more control?** See [docs/INSTALLATION.md](docs/INSTALLATION.md) for manual installation and production deployment.

## Tech Stack

**Backend:**
- PHP 8.1+
- [Slim Framework 4](https://www.slimframework.com/) - Lightweight routing and middleware
- [Guzzle](https://docs.guzzlephp.org/) - HTTP client for Strava API
- [League OAuth2 Client](https://oauth2-client.thephpleague.com/) - OAuth authentication
- [Carbon](https://carbon.nesbot.com/) - Date/time handling
- [Monolog](https://github.com/Seldaek/monolog) - Logging

**Frontend:**
- [Chart.js](https://www.chartjs.org/) - Interactive data visualizations
- [Vite](https://vitejs.dev/) - Build tool and bundler
- Vanilla JavaScript - No heavy frameworks

**Development:**
- Composer - PHP dependency management
- npm - JavaScript package management
- PHPUnit - Testing framework

## Documentation

**For Users:**
- [Installation Guide](docs/INSTALLATION.md) - Setup, configuration, and deployment

**For Developers:**
- [Requirements](docs/requirements.md) - Detailed feature requirements
- [Development Plan](docs/plan.md) - Implementation roadmap
- [Task Checklist](docs/tasks.md) - Development progress

## License

Apache License 2.0 - See [LICENSE](LICENSE) for details.
