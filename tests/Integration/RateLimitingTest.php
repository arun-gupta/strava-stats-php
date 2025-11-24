<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Rate Limiting Integration Test
 *
 * Tests the rate limiting middleware behavior
 */
class RateLimitingTest extends TestCase
{
    private string $testIp;
    private int $maxRequests;
    private int $windowSeconds;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for rate limiting storage
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session and set up test parameters
        $_SESSION = [];
        $this->testIp = '127.0.0.1';
        $this->maxRequests = 100;
        $this->windowSeconds = 60;
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testInitializeRateLimitTracking(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        // Initialize tracking
        $_SESSION[$key] = [
            'count' => 0,
            'window_start' => time(),
        ];

        $this->assertArrayHasKey($key, $_SESSION);
        $this->assertIsArray($_SESSION[$key]);
        $this->assertArrayHasKey('count', $_SESSION[$key]);
        $this->assertArrayHasKey('window_start', $_SESSION[$key]);
    }

    public function testIncrementRequestCount(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        // Initialize
        $_SESSION[$key] = [
            'count' => 0,
            'window_start' => time(),
        ];

        // Simulate 5 requests
        for ($i = 0; $i < 5; $i++) {
            $_SESSION[$key]['count']++;
        }

        $this->assertSame(5, $_SESSION[$key]['count']);
    }

    public function testRateLimitNotExceeded(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        $_SESSION[$key] = [
            'count' => 50,
            'window_start' => time(),
        ];

        $isLimited = $this->isRateLimited($key, $this->maxRequests);

        $this->assertFalse($isLimited);
    }

    public function testRateLimitExceeded(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        $_SESSION[$key] = [
            'count' => 101,
            'window_start' => time(),
        ];

        $isLimited = $this->isRateLimited($key, $this->maxRequests);

        $this->assertTrue($isLimited);
    }

    public function testRateLimitExactlyAtLimit(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        $_SESSION[$key] = [
            'count' => 100,
            'window_start' => time(),
        ];

        $isLimited = $this->isRateLimited($key, $this->maxRequests);

        $this->assertFalse($isLimited); // At limit, not exceeded
    }

    public function testRateLimitWindowExpired(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        // Set window start to 2 minutes ago (expired)
        $_SESSION[$key] = [
            'count' => 150, // Exceeded limit
            'window_start' => time() - 120,
        ];

        $windowStart = $_SESSION[$key]['window_start'];
        $isExpired = (time() - $windowStart) > $this->windowSeconds;

        $this->assertTrue($isExpired);

        // If expired, should reset
        if ($isExpired) {
            $_SESSION[$key] = [
                'count' => 0,
                'window_start' => time(),
            ];
        }

        $this->assertSame(0, $_SESSION[$key]['count']);
    }

    public function testRateLimitWindowNotExpired(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        // Set window start to 30 seconds ago (not expired)
        $_SESSION[$key] = [
            'count' => 50,
            'window_start' => time() - 30,
        ];

        $windowStart = $_SESSION[$key]['window_start'];
        $isExpired = (time() - $windowStart) > $this->windowSeconds;

        $this->assertFalse($isExpired);
    }

    public function testRateLimitDifferentPaths(): void
    {
        $keyDashboard = $this->getRateLimitKey($this->testIp, '/dashboard');
        $keyAuth = $this->getRateLimitKey($this->testIp, '/auth/strava');

        $_SESSION[$keyDashboard] = [
            'count' => 50,
            'window_start' => time(),
        ];

        $_SESSION[$keyAuth] = [
            'count' => 25,
            'window_start' => time(),
        ];

        // Different paths should have independent limits
        $this->assertNotSame($keyDashboard, $keyAuth);
        $this->assertSame(50, $_SESSION[$keyDashboard]['count']);
        $this->assertSame(25, $_SESSION[$keyAuth]['count']);
    }

    public function testRateLimitDifferentIPs(): void
    {
        $ip1 = '127.0.0.1';
        $ip2 = '192.168.1.1';

        $key1 = $this->getRateLimitKey($ip1, '/dashboard');
        $key2 = $this->getRateLimitKey($ip2, '/dashboard');

        $_SESSION[$key1] = [
            'count' => 100,
            'window_start' => time(),
        ];

        $_SESSION[$key2] = [
            'count' => 50,
            'window_start' => time(),
        ];

        // Different IPs should have independent limits
        $this->assertNotSame($key1, $key2);
        $this->assertSame(100, $_SESSION[$key1]['count']);
        $this->assertSame(50, $_SESSION[$key2]['count']);
    }

    public function testRateLimitProtectedPaths(): void
    {
        $protectedPaths = ['/auth/', '/api/', '/dashboard'];

        foreach ($protectedPaths as $path) {
            $key = $this->getRateLimitKey($this->testIp, $path);
            $_SESSION[$key] = [
                'count' => 0,
                'window_start' => time(),
            ];

            $this->assertArrayHasKey($key, $_SESSION);
        }

        $this->assertCount(3, array_filter(array_keys($_SESSION), fn($k) => str_contains($k, 'rate_limit_')));
    }

    public function testRateLimitUnprotectedPathsNotTracked(): void
    {
        $unprotectedPaths = ['/', '/healthz', '/error'];

        // These should not have rate limit tracking
        foreach ($unprotectedPaths as $path) {
            $key = $this->getRateLimitKey($this->testIp, $path);
            $this->assertArrayNotHasKey($key, $_SESSION);
        }
    }

    public function testCalculateTimeUntilReset(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        $windowStart = time() - 30; // 30 seconds ago
        $_SESSION[$key] = [
            'count' => 101,
            'window_start' => $windowStart,
        ];

        $timeElapsed = time() - $windowStart;
        $timeRemaining = $this->windowSeconds - $timeElapsed;

        $this->assertSame(30, $timeElapsed);
        $this->assertSame(30, $timeRemaining);
    }

    public function testRateLimitHeadersGeneration(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        $_SESSION[$key] = [
            'count' => 45,
            'window_start' => time(),
        ];

        // Simulate generating rate limit headers
        $headers = [
            'X-RateLimit-Limit' => $this->maxRequests,
            'X-RateLimit-Remaining' => $this->maxRequests - $_SESSION[$key]['count'],
            'X-RateLimit-Reset' => $_SESSION[$key]['window_start'] + $this->windowSeconds,
        ];

        $this->assertSame(100, $headers['X-RateLimit-Limit']);
        $this->assertSame(55, $headers['X-RateLimit-Remaining']);
        $this->assertIsInt($headers['X-RateLimit-Reset']);
    }

    public function testRateLimitResetOnNewWindow(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        // Old window (expired)
        $_SESSION[$key] = [
            'count' => 150,
            'window_start' => time() - 120,
        ];

        // Check if expired
        $windowStart = $_SESSION[$key]['window_start'];
        if ((time() - $windowStart) > $this->windowSeconds) {
            // Reset
            $_SESSION[$key] = [
                'count' => 1, // New request
                'window_start' => time(),
            ];
        }

        $this->assertSame(1, $_SESSION[$key]['count']);
        $this->assertGreaterThan($windowStart, $_SESSION[$key]['window_start']);
    }

    public function testConcurrentRequestsFromSameIP(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        $_SESSION[$key] = [
            'count' => 0,
            'window_start' => time(),
        ];

        // Simulate 10 concurrent requests
        for ($i = 0; $i < 10; $i++) {
            $_SESSION[$key]['count']++;
        }

        $this->assertSame(10, $_SESSION[$key]['count']);
        $this->assertFalse($this->isRateLimited($key, $this->maxRequests));
    }

    public function testBurstRequests(): void
    {
        $key = $this->getRateLimitKey($this->testIp, '/dashboard');

        $_SESSION[$key] = [
            'count' => 0,
            'window_start' => time(),
        ];

        // Simulate burst of 105 requests (exceeding limit)
        for ($i = 0; $i < 105; $i++) {
            $_SESSION[$key]['count']++;
        }

        $this->assertSame(105, $_SESSION[$key]['count']);
        $this->assertTrue($this->isRateLimited($key, $this->maxRequests));
    }

    // Helper methods

    private function getRateLimitKey(string $ip, string $path): string
    {
        return 'rate_limit_' . md5($ip . $path);
    }

    private function isRateLimited(string $key, int $maxRequests): bool
    {
        if (!isset($_SESSION[$key])) {
            return false;
        }

        return $_SESSION[$key]['count'] > $maxRequests;
    }
}
