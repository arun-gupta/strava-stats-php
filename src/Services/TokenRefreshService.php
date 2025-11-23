<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Token Refresh Service
 *
 * Handles automatic refresh of expired Strava OAuth tokens
 */
class TokenRefreshService
{
    /**
     * Refresh access token using refresh token
     *
     * @param string $refreshToken The refresh token
     * @return array|null Token data with new access_token, refresh_token, and expires_at, or null on failure
     */
    public static function refreshToken(string $refreshToken): ?array
    {
        // Load OAuth configuration
        $config = require __DIR__ . '/../../config/oauth.php';
        $stravaConfig = $config['strava'];

        try {
            $client = new Client();
            $response = $client->post($stravaConfig['token_url'], [
                'form_params' => [
                    'client_id' => $stravaConfig['client_id'],
                    'client_secret' => $stravaConfig['client_secret'],
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ],
            ]);

            $tokenData = json_decode($response->getBody()->getContents(), true);

            // Update session with new tokens
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['access_token'] = $tokenData['access_token'];
            $_SESSION['refresh_token'] = $tokenData['refresh_token'];
            $_SESSION['expires_at'] = $tokenData['expires_at'];

            // Log successful refresh
            Logger::info('Token refreshed successfully', [
                'expires_at' => date('Y-m-d H:i:s', $tokenData['expires_at']),
            ]);

            return $tokenData;

        } catch (GuzzleException $e) {
            // Log error
            Logger::error('Token refresh failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if token is expired or about to expire
     *
     * @param int|null $expiresAt Unix timestamp when token expires
     * @param int $bufferSeconds Seconds before expiry to consider token expired (default: 300 = 5 minutes)
     * @return bool True if token is expired or about to expire
     */
    public static function isTokenExpired(?int $expiresAt, int $bufferSeconds = 300): bool
    {
        if ($expiresAt === null || $expiresAt === 0) {
            return false;
        }

        // Consider token expired if it expires within buffer period
        return time() >= ($expiresAt - $bufferSeconds);
    }

    /**
     * Refresh token if needed
     *
     * Checks if token is expired and refreshes automatically
     *
     * @return bool True if token is valid (or was successfully refreshed), false otherwise
     */
    public static function refreshIfNeeded(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $expiresAt = $_SESSION['expires_at'] ?? null;
        $refreshToken = $_SESSION['refresh_token'] ?? null;

        // If no refresh token, can't refresh
        if (!$refreshToken) {
            return false;
        }

        // Check if token needs refresh
        if (self::isTokenExpired($expiresAt)) {
            Logger::info('Token expired or expiring soon, refreshing...');

            $result = self::refreshToken($refreshToken);
            return $result !== null;
        }

        return true; // Token is still valid
    }
}
