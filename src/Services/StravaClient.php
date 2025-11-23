<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * StravaClient
 *
 * HTTP client for interacting with Strava API
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
        try {
            $response = $this->httpClient->get('/athlete', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data;

        } catch (GuzzleException $e) {
            error_log('Failed to fetch athlete profile: ' . $e->getMessage());
            return null;
        }
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
        try {
            $response = $this->httpClient->get('/athlete/activities', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data;

        } catch (GuzzleException $e) {
            error_log('Failed to fetch activities: ' . $e->getMessage());
            return null;
        }
    }
}
