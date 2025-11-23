<?php

declare(strict_types=1);

namespace App\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    private static ?MonologLogger $instance = null;

    public static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/logging.php';

            self::$instance = new MonologLogger($config['name']);

            $logPath = $config['path'];
            $logDir = dirname($logPath);

            // Ensure log directory exists
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            self::$instance->pushHandler(
                new StreamHandler($logPath, MonologLogger::DEBUG)
            );
        }

        return self::$instance;
    }
}
