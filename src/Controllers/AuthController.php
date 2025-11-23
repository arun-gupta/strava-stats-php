<?php

declare(strict_types=1);

namespace App\Controllers;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * AuthController
 *
 * Handles OAuth authentication flow with Strava API
 */
class AuthController
{
    /**
     * Initiate OAuth authorization flow
     *
     * Redirects user to Strava's authorization page
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function authorize(Request $request, Response $response): Response
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Load OAuth configuration
        $config = require __DIR__ . '/../../config/oauth.php';
        $stravaConfig = $config['strava'];

        // Generate state parameter for CSRF protection
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;

        // Generate PKCE code verifier and challenge
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        // Store code verifier for callback
        $_SESSION['oauth_code_verifier'] = $codeVerifier;

        // Build authorization URL
        $authUrl = $stravaConfig['authorize_url'] . '?' . http_build_query([
            'client_id' => $stravaConfig['client_id'],
            'redirect_uri' => $stravaConfig['redirect_uri'],
            'response_type' => $stravaConfig['response_type'],
            'approval_prompt' => $stravaConfig['approval_prompt'],
            'scope' => implode(',', $stravaConfig['scopes']),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        // Redirect to Strava authorization page
        return $response
            ->withHeader('Location', $authUrl)
            ->withStatus(302);
    }

    /**
     * Generate PKCE code verifier
     *
     * @return string
     */
    private function generateCodeVerifier(): string
    {
        $randomBytes = random_bytes(32);
        return rtrim(strtr(base64_encode($randomBytes), '+/', '-_'), '=');
    }

    /**
     * Generate PKCE code challenge from verifier
     *
     * @param string $verifier
     * @return string
     */
    private function generateCodeChallenge(string $verifier): string
    {
        $hash = hash('sha256', $verifier, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    /**
     * Handle OAuth callback from Strava
     *
     * Exchanges authorization code for access token
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function callback(Request $request, Response $response): Response
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get query parameters
        $queryParams = $request->getQueryParams();
        $code = $queryParams['code'] ?? null;
        $state = $queryParams['state'] ?? null;
        $error = $queryParams['error'] ?? null;

        // Handle error from Strava (user denied access)
        if ($error) {
            // Map error to user-friendly page
            $errorType = $error === 'access_denied' ? 'access_denied' : 'api_error';
            return $response
                ->withHeader('Location', '/error?error=' . urlencode($errorType))
                ->withStatus(302);
        }

        // Validate required parameters
        if (!$code || !$state) {
            return $response
                ->withHeader('Location', '/error?error=missing_parameters')
                ->withStatus(302);
        }

        // Validate state parameter (CSRF protection)
        if (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
            // Clear session and redirect with error
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_code_verifier']);
            return $response
                ->withHeader('Location', '/error?error=invalid_state')
                ->withStatus(302);
        }

        // Get code verifier from session
        $codeVerifier = $_SESSION['oauth_code_verifier'] ?? null;
        if (!$codeVerifier) {
            return $response
                ->withHeader('Location', '/error?error=missing_verifier')
                ->withStatus(302);
        }

        // Load OAuth configuration
        $config = require __DIR__ . '/../../config/oauth.php';
        $stravaConfig = $config['strava'];

        // Exchange authorization code for access token
        try {
            $client = new Client();
            $tokenResponse = $client->post($stravaConfig['token_url'], [
                'form_params' => [
                    'client_id' => $stravaConfig['client_id'],
                    'client_secret' => $stravaConfig['client_secret'],
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'code_verifier' => $codeVerifier,
                ],
            ]);

            $tokenData = json_decode($tokenResponse->getBody()->getContents(), true);

            // Store tokens and athlete data in session
            $_SESSION['access_token'] = $tokenData['access_token'];
            $_SESSION['refresh_token'] = $tokenData['refresh_token'];
            $_SESSION['expires_at'] = $tokenData['expires_at'];
            $_SESSION['athlete'] = $tokenData['athlete'] ?? [];

            // Clean up OAuth state
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_code_verifier']);

            // Redirect to dashboard
            return $response
                ->withHeader('Location', '/dashboard')
                ->withStatus(302);

        } catch (\Exception $e) {
            // Log error and redirect with error message
            error_log('OAuth token exchange failed: ' . $e->getMessage());
            return $response
                ->withHeader('Location', '/error?error=token_exchange_failed')
                ->withStatus(302);
        }
    }

    /**
     * Sign out user
     *
     * Clears session and tokens, redirects to home
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function signout(Request $request, Response $response): Response
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear all session data
        $_SESSION = [];

        // Destroy the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();

        // Redirect to home page
        return $response
            ->withHeader('Location', '/?signed_out=1')
            ->withStatus(302);
    }
}
