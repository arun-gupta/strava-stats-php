<?php

declare(strict_types=1);

/**
 * OAuth Configuration for Strava API
 *
 * This configuration defines the OAuth2 settings for authenticating
 * with Strava's API using the Authorization Code flow.
 */

return [
    'strava' => [
        // Client credentials from Strava app settings
        'client_id' => $_ENV['STRAVA_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['STRAVA_CLIENT_SECRET'] ?? '',
        'redirect_uri' => $_ENV['STRAVA_REDIRECT_URI'] ?? '',

        // Strava OAuth endpoints
        'authorize_url' => 'https://www.strava.com/oauth/authorize',
        'token_url' => 'https://www.strava.com/oauth/token',
        'api_base_url' => 'https://www.strava.com/api/v3',

        // Requested OAuth scopes
        // read: Read public profile data
        // activity:read_all: Read all activities (including private)
        'scopes' => [
            'read',
            'activity:read_all',
        ],

        // OAuth response type (always 'code' for Authorization Code flow)
        'response_type' => 'code',

        // Whether to force approval prompt (useful for testing)
        'approval_prompt' => 'auto', // 'auto' or 'force'
    ],
];
