<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * StravaClient
 *
 * HTTP client for interacting with Strava API
 * Handles automatic token refresh on 401 responses
 */
class StravaClient
{
    private Client $httpClient;
    private string $apiBaseUrl;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/oauth.php';
        $this->apiBaseUrl = $config['strava']['api_base_url'];

        $this->httpClient = new Client([
            'base_uri' => $this->apiBaseUrl,
            'timeout' => 10.0,
        ]);
    }

    /**
     * Get authenticated athlete's profile
     *
     * @param string $accessToken
     * @return array|null
     */
    public function getAthlete(string $accessToken): ?array
    {
        return $this->makeRequestWithRetry(function($token) {
            return $this->httpClient->get('/athlete', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
        }, $accessToken, 'athlete profile');
    }

    /**
     * Get athlete's activities
     *
     * @param string $accessToken
     * @param int $page
     * @param int $perPage
     * @return array|null
     */
    public function getActivities(string $accessToken, int $page = 1, int $perPage = 30): ?array
    {
        return $this->makeRequestWithRetry(function($token) use ($page, $perPage) {
            return $this->httpClient->get('/athlete/activities', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage,
                ],
            ]);
        }, $accessToken, 'activities');
    }

    /**
     * Make HTTP request with automatic retry on specific errors
     *
     * @param callable $requestCallback Function that makes the request
     * @param string $accessToken Current access token
     * @param string $requestName Name for logging
     * @param int $retryCount Current retry attempt (for exponential backoff)
     * @return array|null Response data or null on failure
     */
    private function makeRequestWithRetry(callable $requestCallback, string $accessToken, string $requestName, int $retryCount = 0): ?array
    {
        $maxRetries = 3;

        try {
            $response = $requestCallback($accessToken);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            // Handle 401 Unauthorized - Token expired
            if ($statusCode === 401) {
                Logger::info("Received 401 for $requestName, attempting token refresh");

                // Try to refresh token
                if (TokenRefreshService::refreshIfNeeded()) {
                    // Get new token from session
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $newToken = $_SESSION['access_token'] ?? null;

                    if ($newToken) {
                        // Retry request with new token
                        try {
                            $response = $requestCallback($newToken);
                            $data = json_decode($response->getBody()->getContents(), true);
                            Logger::info("Request retry successful after token refresh");
                            return $data;
                        } catch (GuzzleException $retryException) {
                            Logger::error("Request retry failed: " . $retryException->getMessage());
                            return null;
                        }
                    }
                }

                Logger::error("Token refresh failed, cannot retry request");
                return null;
            }

            // Handle 429 Too Many Requests - Rate limiting
            if ($statusCode === 429 && $retryCount < $maxRetries) {
                $retryAfter = $e->getResponse()->getHeaderLine('Retry-After') ?: 5;
                $waitTime = is_numeric($retryAfter) ? (int)$retryAfter : 5;

                Logger::warning("Rate limited for $requestName, waiting {$waitTime}s before retry");
                sleep($waitTime);

                return $this->makeRequestWithRetry($requestCallback, $accessToken, $requestName, $retryCount + 1);
            }

            // Handle 403 Forbidden - Insufficient permissions
            if ($statusCode === 403) {
                Logger::error("Insufficient permissions for $requestName - check OAuth scopes");
                return null;
            }

            // Handle 404 Not Found - Resource doesn't exist (don't retry)
            if ($statusCode === 404) {
                Logger::warning("Resource not found for $requestName");
                return null;
            }

            // Other 4xx client errors (don't retry)
            Logger::error("Client error ($statusCode) for $requestName: " . $e->getMessage());
            return null;

        } catch (GuzzleException $e) {
            // Handle 5xx server errors with exponential backoff
            if ($retryCount < $maxRetries) {
                $waitTime = pow(2, $retryCount); // Exponential backoff: 1s, 2s, 4s
                Logger::warning("Server error for $requestName, retrying in {$waitTime}s (attempt " . ($retryCount + 1) . "/$maxRetries)");
                sleep($waitTime);

                return $this->makeRequestWithRetry($requestCallback, $accessToken, $requestName, $retryCount + 1);
            }

            Logger::error("Failed to fetch $requestName after $maxRetries retries: " . $e->getMessage());
            return null;
        }
    }
}
