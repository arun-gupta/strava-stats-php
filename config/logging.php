<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
    'name' => 'strava-stats',
    'level' => $_ENV['LOG_LEVEL'] ?? 'debug',
    'path' => __DIR__ . '/../' . ($_ENV['LOG_PATH'] ?? 'logs/app.log'),

    'channels' => [
        'app' => [
            'handler' => StreamHandler::class,
            'level' => Logger::DEBUG,
        ],
        'api' => [
            'handler' => StreamHandler::class,
            'level' => Logger::INFO,
        ],
    ],
];
