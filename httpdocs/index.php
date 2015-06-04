<?php

/**
 * Example index.php you can copy and reuse.
 * Be sure to change paths to reflect your server environment.
 */

$base = dirname(__DIR__);
set_include_path(join(PATH_SEPARATOR, [
    // Public root, just in case crappy plugins need it:
    "$base/httpdocs",
    // Source for this project:
    "$base/src",
    // Vendor (Composer modules):
    "$base/vendor",
]));

// Require and setup the Composer autoloader:
$autoloader = require_once 'autoload.php';
$autoloader->setUseIncludePath(true);

// Dependencies and routing:
require_once 'dependencies.php';
require_once 'routing.php';

try {
    $state = $router->resolve(
        $_SERVER['REQUEST_URI'],
        $_SERVER['REQUEST_METHOD']
    );
    switch ($state->group()) {
        default:
            echo $state->run();
            break;
    }
} catch (Exception $e) {
    // You should do something useful here...
}

