<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Laravel root when public files are in public_html (repo is at ~/tyre/)
$laravelRoot = dirname(__DIR__) . '/tyre/admin';

if (!file_exists($laravelRoot . '/vendor/autoload.php')) {
    die('Laravel not found. Ensure repo is at ~/tyre and run: bash install-cpanel.sh');
}

if (file_exists($maintenance = $laravelRoot . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelRoot . '/vendor/autoload.php';
$app = require_once $laravelRoot . '/bootstrap/app.php';
$app->handleRequest(Request::capture());
