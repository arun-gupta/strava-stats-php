<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * SecurityHeadersMiddleware
 *
 * Adds security headers to all HTTP responses
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        // Prevent clickjacking attacks
        $response = $response->withHeader('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection (for older browsers)
        $response = $response->withHeader('X-XSS-Protection', '1; mode=block');

        // Referrer policy - don't leak sensitive info in referrer
        $response = $response->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https://*.strava.com https://strava.com https://*.cloudfront.net",
            "connect-src 'self' https://www.strava.com https://strava.com https://cdn.jsdelivr.net",
            "font-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self' https://www.strava.com",
        ];
        $response = $response->withHeader('Content-Security-Policy', implode('; ', $csp));

        // Permissions Policy (formerly Feature Policy)
        $permissions = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
        ];
        $response = $response->withHeader('Permissions-Policy', implode(', ', $permissions));

        // HTTPS enforcement header (only in production)
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
            // Strict-Transport-Security: enforce HTTPS for 1 year
            $response = $response->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
