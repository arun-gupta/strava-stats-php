<?php

declare(strict_types=1);

use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    // Health check endpoint (public, no auth required)
    $app->get('/healthz', function (Request $request, Response $response) {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => 'strava-stats-php',
            'version' => '1.0.0',
        ];

        $response->getBody()->write(json_encode($health));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    // Timezone endpoint (public - used to set user timezone)
    $app->post('/api/timezone', function (Request $request, Response $response) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $body = $request->getParsedBody();
        $timezone = $body['timezone'] ?? null;

        // Validate timezone
        if ($timezone && in_array($timezone, timezone_identifiers_list())) {
            $_SESSION['user_timezone'] = $timezone;
            date_default_timezone_set($timezone);

            $response->getBody()->write(json_encode(['success' => true]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }

        $response->getBody()->write(json_encode(['success' => false, 'error' => 'Invalid timezone']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    });

    // Protected API routes will be added here with ->add(new AuthMiddleware())
};
