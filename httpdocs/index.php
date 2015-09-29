<?php

/**
 * Example index.php you can extend on.
 * Be sure to change paths to reflect your server environment.
 */

// Require and setup the Composer autoloader:
$autoloader = require_once '../vendor/autoload.php';

// Dependencies and routing:
require_once '../src/dependencies.php';
require_once '../src/routing.php';

try {
    if (!($state = $router->resolve(
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_METHOD']
    ))) {
        throw new Exception;
    }
    switch ($state->group()) {
        default:
            echo $state->run();
            break;
    }
} catch (Exception $e) {
    // You should do something useful here...
}

