<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    $envFile = dirname(__DIR__).'/.env';
    if (file_exists($envFile)) {
        (new Dotenv())->bootEnv($envFile);
    } else {
        // Set default values for CI/CD environment
        $_SERVER['APP_ENV'] = $_SERVER['APP_ENV'] ?? 'test';
        $_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? '0';
        $_SERVER['APP_SECRET'] = $_SERVER['APP_SECRET'] ?? 'test_secret';
        $_SERVER['DATABASE_URL'] = $_SERVER['DATABASE_URL'] ?? 'sqlite:///:memory:';
    }
}

if (isset($_SERVER['APP_DEBUG']) && $_SERVER['APP_DEBUG']) {
    umask(0000);
}
