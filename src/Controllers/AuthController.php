<?php

declare(strict_types=1);

namespace App\Controllers;

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
        // TODO: Implement OAuth callback handling (Phase 1.3)
        // - Validate state parameter
        // - Exchange code for tokens
        // - Store tokens in session
        // - Redirect to dashboard

        $response->getBody()->write('OAuth callback - Coming Soon');
        return $response;
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
        // TODO: Implement sign out (Phase 1.6)
        // - Clear session
        // - Clear stored tokens
        // - Redirect to home page

        $response->getBody()->write('Sign out - Coming Soon');
        return $response;
    }
}
