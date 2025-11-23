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

    // Test endpoint to debug Strava API
    $app->get('/test-strava', function (Request $request, Response $response) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $accessToken = $_SESSION['access_token'] ?? null;

        if (!$accessToken) {
            $response->getBody()->write('No access token in session');
            return $response;
        }

        // Make a raw curl request to Strava
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.strava.com/api/v3/athlete/activities?per_page=5');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $output = [
            'http_code' => $httpCode,
            'error' => $error ?: null,
            'token_prefix' => substr($accessToken, 0, 10) . '...',
            'response' => $result,
        ];

        $response->getBody()->write('<pre>' . htmlspecialchars(json_encode($output, JSON_PRETTY_PRINT)) . '</pre>');
        return $response;
    });

    // Dashboard (protected with middleware)
    $app->get('/dashboard', function (Request $request, Response $response) {
        // Start session to get access token
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $accessToken = $_SESSION['access_token'] ?? null;
        $activities = [];
        $activityCounts = [];

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
