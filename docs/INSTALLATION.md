# Installation Guide

This guide covers manual installation, running the application, and production deployment.

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

## Manual Installation

If you prefer to install manually instead of using the quickstart script:

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

Copy the output and set it as `SESSION_SECRET` in your `.env` file.

## Running the Application

### Development Server

Using PHP built-in server:
```bash
php -S localhost:8080 -t public
```

Access the application at http://localhost:8080

### Using Docker (Optional)

A Dockerfile will be provided in future releases for containerized deployment.

## Production Deployment

### Prerequisites

For production, you should use a proper web server like Nginx or Apache with PHP-FPM instead of the built-in PHP server.

### Nginx Configuration

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/strava-stats-php/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache Configuration

Example Apache configuration with `.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

### Environment Configuration

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure proper logging paths
4. Ensure file permissions are correct:
   ```bash
   chmod -R 755 /path/to/strava-stats-php
   chmod -R 775 logs cache
   ```

### SSL/TLS Configuration

For production, always use HTTPS:

1. Obtain an SSL certificate (e.g., from Let's Encrypt)
2. Configure your web server to use SSL
3. Update `APP_URL` and `STRAVA_REDIRECT_URI` to use `https://`
4. Update your Strava app settings to use the HTTPS redirect URI

### Performance Optimization

1. **Enable OPcache**: Ensure PHP OPcache is enabled in production
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   opcache.validate_timestamps=0
   ```

2. **Composer autoloader optimization**:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Cache compiled assets**: The `npm run build` command creates optimized production assets

4. **Configure caching**: Adjust `CACHE_TTL` in `.env` based on your needs

### Monitoring and Maintenance

1. Monitor logs in the `logs/` directory
2. Set up log rotation for `logs/app.log`
3. Monitor the `/healthz` endpoint for availability checks
4. Set up error tracking (e.g., Sentry) if needed

### Security Checklist

- [ ] `APP_DEBUG` is set to `false`
- [ ] Strong `SESSION_SECRET` is configured (generate with `openssl rand -base64 32`)
- [ ] HTTPS is enabled with valid SSL certificate
- [ ] File permissions are properly set (755 for directories, 644 for files)
- [ ] `.env` file is not publicly accessible (should be outside web root or denied by server)
- [ ] Web server is configured to deny access to sensitive files (`.git/`, `composer.json`, etc.)
- [ ] PHP version is up to date with security patches
- [ ] Dependencies are updated regularly (`composer update`, `npm update`)
- [ ] Session cookies configured with HttpOnly, Secure, and SameSite flags (already configured)
- [x] Rate limiting middleware enabled (protects /auth/, /api/, /dashboard - 100 req/min)
- [x] Security headers configured (CSP, HSTS, X-Frame-Options, etc.)
- [x] Input validation on all user inputs (dates, query parameters)

### Rate Limiting

The application includes built-in rate limiting to protect against abuse and brute force attacks:

- **Default Limits**: 100 requests per 60 seconds per IP address
- **Protected Endpoints**: `/auth/`, `/api/`, `/dashboard`
- **Configuration**: Edit `public/index.php` to adjust limits:
  ```php
  $app->add(new \App\Middleware\RateLimitMiddleware(
      maxRequests: 100,        // Adjust as needed
      windowSeconds: 60,       // Adjust as needed
      protectedPaths: [        // Add/remove paths
          '/auth/',
          '/api/',
          '/dashboard'
      ]
  ));
  ```

**Rate Limit Headers**: All responses include standard rate limit headers:
- `X-RateLimit-Limit`: Maximum requests allowed in window
- `X-RateLimit-Remaining`: Number of requests remaining
- `X-RateLimit-Reset`: Unix timestamp when the limit resets

**Rate Limit Response** (429 Too Many Requests):
```json
{
  "error": "Rate limit exceeded",
  "message": "Too many requests. Please try again later.",
  "retry_after": 45
}
```

**Production Recommendations**:
- For high-traffic deployments, consider using Redis for rate limit storage instead of sessions
- Adjust limits based on your usage patterns and server capacity
- Monitor rate limit violations in logs to detect potential attacks

### Quick Deployment Checklist

**Pre-Deployment:**
1. Test locally with production settings
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm run build` for production assets
4. Set environment variables in `.env`
5. Generate strong `SESSION_SECRET`

**Deployment:**
1. Upload files to server (exclude `node_modules/`, `.git/`)
2. Configure web server (Nginx/Apache)
3. Set up SSL certificate (Let's Encrypt recommended)
4. Update Strava app callback URL to production domain
5. Set proper file permissions

**Post-Deployment:**
1. Test OAuth flow end-to-end
2. Verify all dashboard features work
3. Check logs for errors
4. Monitor `/healthz` endpoint
5. Set up automated backups for `.env` file

### Updating the Application

To update to the latest version:

```bash
# 1. Pull latest changes
git pull origin main

# 2. Update dependencies
composer install --optimize-autoloader --no-dev
npm install

# 3. Rebuild assets
npm run build

# 4. Clear any caches (if applicable)
# 5. Restart PHP-FPM if needed
sudo systemctl restart php8.1-fpm
```

## Troubleshooting

### Common Issues

**Issue**: "Class not found" errors
- **Solution**: Run `composer dump-autoload`

**Issue**: Front-end assets not loading
- **Solution**: Run `npm run build` and check that `public/build/` exists

**Issue**: Permission denied errors
- **Solution**: Check file permissions on `logs/` and `cache/` directories

**Issue**: OAuth redirect mismatch
- **Solution**: Ensure `STRAVA_REDIRECT_URI` in `.env` matches exactly what's configured in Strava app settings

For more help, check the [GitHub Issues](https://github.com/arun-gupta/strava-stats-php/issues) or create a new issue.
