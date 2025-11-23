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
        // TODO: Implement OAuth authorization flow (Phase 1.2)
        // - Generate state parameter for CSRF protection
        // - Generate PKCE code challenge
        // - Build authorization URL
        // - Redirect to Strava

        $response->getBody()->write('OAuth authorization - Coming Soon');
        return $response;
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
