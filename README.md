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

## Framework Decision

This project uses **Slim Framework 4** for the following reasons:
- Lightweight and focused on HTTP routing and middleware
- Minimal overhead, ideal for API-first applications
- PSR-7/PSR-15 compliant (HTTP messages and request handlers)
- Flexible architecture with explicit dependency injection
- Easy to understand and maintain for a single-purpose analytics app

Laravel was considered but deemed too heavy for this focused use case where we primarily need OAuth handling, API endpoints, and basic views.

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js and npm (for front-end assets)

### Installing Composer

On macOS using Homebrew:
```bash
brew install composer
```

On other platforms, see https://getcomposer.org/download/

## Installation

### Manual Installation

1. Clone the repository:
```bash
git clone https://github.com/arun-gupta/strava-stats-php.git
cd strava-stats-php
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Build front-end assets:
```bash
npm run build
```

## Configuration

1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Configure your Strava API credentials in `.env`:
   - Register your application at https://www.strava.com/settings/api
   - Set `STRAVA_CLIENT_ID` and `STRAVA_CLIENT_SECRET`
   - Set `STRAVA_REDIRECT_URI` to match your OAuth callback URL

3. Generate a secure session secret:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

## Running the Application

### Development Server

Using PHP built-in server:
```bash
php -S localhost:8080 -t public
```

Access the application at http://localhost:8080

### Production Deployment

For production, use a proper web server like Nginx or Apache with PHP-FPM. See the `docs/` directory for deployment guides.

## Project Structure

```
strava-stats-php/
├── config/          # Configuration files
├── public/          # Public web root
│   └── index.php    # Application entry point
├── resources/       # Front-end assets (JS, CSS)
├── routes/          # Route definitions
│   ├── api.php      # API routes
│   └── web.php      # Web routes
├── src/             # PHP source code
│   ├── Controllers/ # Request handlers
│   ├── Middleware/  # HTTP middleware
│   ├── Models/      # Data models
│   └── Services/    # Business logic
├── tests/           # Test suite
└── views/           # HTML templates
```

## Usage

1. Start the development server
2. Navigate to http://localhost:8080
3. Click "Connect with Strava" to authenticate
4. View your activity analytics on the dashboard

## License

Apache License 2.0 - See [LICENSE](LICENSE) for details.
