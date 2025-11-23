<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * Authentication Middleware
 *
 * Checks if user has valid session with access token
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
            // Create redirect response to home page
            $response = new SlimResponse();
            return $response
                ->withHeader('Location', '/?error=authentication_required')
                ->withStatus(302);
        }

        // Check if token has expired
        $expiresAt = $_SESSION['expires_at'] ?? 0;
        if ($expiresAt > 0 && time() > $expiresAt) {
            // Token expired - redirect to home
            // Clear session
            session_destroy();
            $response = new SlimResponse();
            return $response
                ->withHeader('Location', '/?error=session_expired')
                ->withStatus(302);
        }

        // User is authenticated, continue to next middleware/handler
        return $handler->handle($request);
    }
}
