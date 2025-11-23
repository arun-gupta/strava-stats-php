# Strava Activity Analyzer

Analyze and visualize your Strava activity statistics with beautiful charts and insights.

## Features

- 📊 Activity distribution by sport type (count and duration)
- 🔥 Training heatmaps with consistency and streak tracking
- 🏃 Running statistics and personal records
- 📈 Mileage and pace trends over time
- 📅 Flexible date ranges (7d, 30d, YTD, All Time, Custom)
- ⚖️ Toggle between metric and imperial units
- 🔒 Secure OAuth2 authentication with Strava

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

## Documentation

**For Users:**
- [Installation Guide](docs/INSTALLATION.md) - Setup, configuration, and deployment

**For Developers:**
- [Requirements](docs/requirements.md) - Detailed feature requirements
- [Development Plan](docs/plan.md) - Implementation roadmap
- [Task Checklist](docs/tasks.md) - Development progress

**Tech Stack:** Built with PHP 8.1+, Slim Framework 4, Chart.js

## License

Apache License 2.0 - See [LICENSE](LICENSE) for details.
