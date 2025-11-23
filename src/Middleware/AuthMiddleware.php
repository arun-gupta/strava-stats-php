<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\TokenRefreshService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * Authentication Middleware
 *
 * Checks if user has valid session with access token
 * Automatically refreshes expired tokens
 * Redirects to home page if not authenticated
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Process request and check authentication
     *
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is authenticated
        if (!isset($_SESSION['access_token'])) {
            // Create redirect response to error page
            $response = new SlimResponse();
            return $response
                ->withHeader('Location', '/error?error=authentication_required')
                ->withStatus(302);
        }

        // Try to refresh token if needed
        $refreshSuccess = TokenRefreshService::refreshIfNeeded();

        // If refresh failed, redirect to error page
        if (!$refreshSuccess) {
            // Clear session
            session_destroy();
            $response = new SlimResponse();
            return $response
                ->withHeader('Location', '/error?error=session_expired')
                ->withStatus(302);
        }

        // User is authenticated, continue to next middleware/handler
        return $handler->handle($request);
    }
}
