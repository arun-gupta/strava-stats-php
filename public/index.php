<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configure secure session settings
ini_set('session.cookie_httponly', '1');  // Prevent JavaScript access to session cookie
ini_set('session.cookie_secure', $_ENV['APP_ENV'] === 'production' ? '1' : '0');  // HTTPS only in production
ini_set('session.cookie_samesite', 'Lax');  // CSRF protection
ini_set('session.use_strict_mode', '1');  // Reject uninitialized session IDs
ini_set('session.gc_maxlifetime', '86400');  // 24 hour session timeout

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
