<?php

/**
 * Example routing setup. This uses the Reroute router; if you prefer something
 * else be our guest (and also change index.php!).
 */

use Reroute\Router;
use Reroute\Url\Flat;

$route = new Router;

$router->state('welcome', new Flat('/'), function () {
    return new Welcome\View;
});

