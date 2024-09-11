<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = 'laravelFiles/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require 'laravelFiles/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once 'laravelFiles/bootstrap/app.php')
    ->handleRequest(Request::capture());
