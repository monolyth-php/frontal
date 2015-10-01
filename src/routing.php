<?php

/**
 * Example routing setup. This uses the Reroute router; if you prefer something
 * else be our guest (and also change index.php!).
 */

use Reroute\Router;

$router = new Router;

$router->state('welcome', '/')->then(function () {
    return new Welcome\View;
});

