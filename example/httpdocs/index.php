<?php

/**
 * Example index.php you can extend on.
 * Be sure to change paths to reflect your server environment.
 */

use Disclosure\Container;
use Cesession\Session;
use Cesession\Handler;
use Monolyth\Utilities;
use Monolyth\HttpController;
use League\Pipeline\Pipeline;

/** Require and setup the Composer autoloader. */
$autoloader = require_once '../vendor/autoload.php';

/** @see Monolyth\Utilities::utf8 */
Utilities::utf8();
/** @see Monolyth\Utilities::proxy */
Utilities::proxy();

require_once '../src/dependencies.php';

$pipeline = new Pipeline;
$controller = new HttpController;
$controller->run();

/**
 * We use Cesession sessions by default; remove or change this if your
 * preferences are different.
 */
$session = new Session('my-session-name');
extract(Container::inject('*', function ($adapter) {}));
$session->registerHandler(new Handler\Pdo($adapter));
session_start();

try {
    if (!($state = $router->resolve($_SERVER['REQUEST_URI']))) {
        throw new Exception('404');
    }
    echo $state();
} catch (Exception $e) {
    // You should do something useful here...
    echo $e->getMessage();
    echo $e->getFile();
    echo $e->getLine();
}

