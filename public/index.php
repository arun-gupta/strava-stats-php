<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(
    displayErrorDetails: $_ENV['APP_DEBUG'] === 'true',
    logErrors: true,
    logErrorDetails: true
);

// Register routes
$webRoutes = require __DIR__ . '/../routes/web.php';
$webRoutes($app);

$apiRoutes = require __DIR__ . '/../routes/api.php';
$apiRoutes($app);

$app->run();
