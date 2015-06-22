<?php

/**
 * Example index.php you can copy and reuse.
 * Be sure to change paths to reflect your server environment.
 */

// Require and setup the Composer autoloader:
$autoloader = require_once '/path/to/vendor/autoload.php';

// Dependencies and routing:
require_once '/path/to/src/dependencies.php';
require_once '/path/to/src/routing.php';

try {
    if (!($state = $router->resolve(
        $_SERVER['REQUEST_URI'],
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

