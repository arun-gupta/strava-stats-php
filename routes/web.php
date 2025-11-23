<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use App\Services\ActivityService;
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

    // Error page
    $app->get('/error', function (Request $request, Response $response) {
        $html = View::render('pages/error', [
            'layout' => 'main',
            'title' => 'Error - Strava Activity Analyzer',
        ]);

        $response->getBody()->write($html);
        return $response;
    });

    // OAuth routes
    $authController = new AuthController();
    $app->get('/auth/strava', [$authController, 'authorize']);
    $app->get('/auth/callback', [$authController, 'callback']);
    $app->get('/signout', [$authController, 'signout']);

    // Dashboard (protected with middleware)
    $app->get('/dashboard', function (Request $request, Response $response) {
        // Start session to get access token
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $accessToken = $_SESSION['access_token'] ?? null;
        $activities = [];
        $activityCounts = [];

        // Debug: Log token info
        if ($accessToken) {
            \App\Services\Logger::info('Dashboard loading with token', [
                'token_length' => strlen($accessToken),
                'token_prefix' => substr($accessToken, 0, 10) . '...',
                'expires_at' => $_SESSION['expires_at'] ?? 'not set',
                'athlete_id' => $_SESSION['athlete']['id'] ?? 'not set',
            ]);
        } else {
            \App\Services\Logger::warning('Dashboard loading WITHOUT access token');
        }

        // Fetch activities if we have a token
        if ($accessToken) {
            $activityService = new ActivityService();
            $activities = $activityService->fetchRecentActivities($accessToken);
            $activityCounts = $activityService->getCountsByType($activities);
        }

        $html = View::render('pages/dashboard', [
            'layout' => 'main',
            'title' => 'Dashboard - Strava Activity Analyzer',
            'activities' => $activities,
            'activityCounts' => $activityCounts,
            'totalActivities' => count($activities),
        ]);

        $response->getBody()->write($html);
        return $response;
    })->add(new AuthMiddleware());
};
