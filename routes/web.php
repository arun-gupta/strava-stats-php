<?php

declare(strict_types=1);

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

    // Dashboard placeholder
    $app->get('/dashboard', function (Request $request, Response $response) {
        $response->getBody()->write('Dashboard - Coming Soon');
        return $response;
    });
};
