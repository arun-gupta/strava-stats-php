<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Services\View;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    // Home page
    $app->get('/', function (Request $request, Response $response) {
        $html = View::render('pages/home', [
            'layout' => 'main',
            'title' => 'Strava Activity Analyzer - Home',
        ]);

        $response->getBody()->write($html);
        return $response;
    });

    // OAuth routes
    $authController = new AuthController();
    $app->get('/auth/strava', [$authController, 'authorize']);
    $app->get('/auth/callback', [$authController, 'callback']);
    $app->get('/signout', [$authController, 'signout']);

    // Dashboard (protected)
    $app->get('/dashboard', function (Request $request, Response $response) {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is authenticated
        if (!isset($_SESSION['access_token'])) {
            // Redirect to home if not authenticated
            return $response
                ->withHeader('Location', '/')
                ->withStatus(302);
        }

        $html = View::render('pages/dashboard', [
            'layout' => 'main',
            'title' => 'Dashboard - Strava Activity Analyzer',
        ]);

        $response->getBody()->write($html);
        return $response;
    });
};
