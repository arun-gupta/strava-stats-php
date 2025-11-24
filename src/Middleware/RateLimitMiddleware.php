<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * RateLimitMiddleware
 *
 * Implements rate limiting to prevent abuse and brute force attacks
 * Uses session storage for simplicity (can be upgraded to Redis for production)
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxRequests;
    private int $windowSeconds;
    private array $protectedPaths;

    /**
     * @param int $maxRequests Maximum number of requests allowed in the time window
     * @param int $windowSeconds Time window in seconds
     * @param array $protectedPaths Paths to apply rate limiting (empty = all paths)
     */
    public function __construct(
        int $maxRequests = 100,
        int $windowSeconds = 60,
        array $protectedPaths = []
    ) {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->protectedPaths = $protectedPaths;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $path = $request->getUri()->getPath();

        // Check if this path should be rate limited
        if (!empty($this->protectedPaths) && !$this->shouldRateLimit($path)) {
            return $handler->handle($request);
        }

        // Get client identifier (IP address)
        $clientId = $this->getClientIdentifier($request);

        // Initialize rate limit data in session
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        // Get current rate limit data for this client
        $rateLimitKey = 'client_' . md5($clientId);
        $currentTime = time();

        if (!isset($_SESSION['rate_limit'][$rateLimitKey])) {
            $_SESSION['rate_limit'][$rateLimitKey] = [
                'count' => 0,
                'reset_at' => $currentTime + $this->windowSeconds,
            ];
        }

        $rateLimitData = $_SESSION['rate_limit'][$rateLimitKey];

        // Reset counter if window has expired
        if ($currentTime >= $rateLimitData['reset_at']) {
            $rateLimitData = [
                'count' => 0,
                'reset_at' => $currentTime + $this->windowSeconds,
            ];
        }

        // Increment request count
        $rateLimitData['count']++;
        $_SESSION['rate_limit'][$rateLimitKey] = $rateLimitData;

        // Check if rate limit exceeded
        if ($rateLimitData['count'] > $this->maxRequests) {
            $response = new \Slim\Psr7\Response();
            $retryAfter = $rateLimitData['reset_at'] - $currentTime;

            $response->getBody()->write(json_encode([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Retry-After', (string)$retryAfter)
                ->withHeader('X-RateLimit-Limit', (string)$this->maxRequests)
                ->withHeader('X-RateLimit-Remaining', '0')
                ->withHeader('X-RateLimit-Reset', (string)$rateLimitData['reset_at'])
                ->withStatus(429);
        }

        // Process request and add rate limit headers
        $response = $handler->handle($request);
        $remaining = $this->maxRequests - $rateLimitData['count'];

        return $response
            ->withHeader('X-RateLimit-Limit', (string)$this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string)max(0, $remaining))
            ->withHeader('X-RateLimit-Reset', (string)$rateLimitData['reset_at']);
    }

    /**
     * Check if the given path should be rate limited
     */
    private function shouldRateLimit(string $path): bool
    {
        foreach ($this->protectedPaths as $protectedPath) {
            if (str_starts_with($path, $protectedPath)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get client identifier (IP address with X-Forwarded-For support)
     */
    private function getClientIdentifier(Request $request): string
    {
        $serverParams = $request->getServerParams();

        // Check for X-Forwarded-For header (when behind a proxy)
        if (isset($serverParams['HTTP_X_FORWARDED_FOR'])) {
            $forwardedFor = $serverParams['HTTP_X_FORWARDED_FOR'];
            // Take the first IP in the chain
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }

        // Check for X-Real-IP header
        if (isset($serverParams['HTTP_X_REAL_IP'])) {
            return $serverParams['HTTP_X_REAL_IP'];
        }

        // Fall back to REMOTE_ADDR
        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }
}
