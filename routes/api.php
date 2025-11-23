<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    // Health check endpoint
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

    // API routes will be added here
};
