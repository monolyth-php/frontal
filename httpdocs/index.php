<?php

/**
 * Example index.php you can extend on.
 * Be sure to change paths to reflect your server environment.
 */

use Disclosure\Container;
use Cesession\Session;
use Cesession\Handler;

// Require and setup the Composer autoloader:
$autoloader = require_once '../vendor/autoload.php';

/**
 * Required to make PHP handle UTF8 in a sane way. To use, simply include
 * somewhere early in your front controller (typically index.php).
 */
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (function_exists('mb_detect_order')) {
    mb_detect_order([
        'CP1251', 'CP1252', 'ISO-8859-1', 'UTF-8',
    ]);
}

/**
 * Correct REMOTE_ADDR if we're behind a proxy.
 * This code is by no means extensive; there's prolly 1.000 other
 * cases you'll want to handle.
 *
 * To use, simply include this somewhere early in your front controller
 * (typically index.php).
 */
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['REMOTE_ADDR'] = trim(array_shift(explode(
       ',',
        $_SERVER['HTTP_X_FORWARDED_FOR']
    )));
}

require_once '../src/dependencies.php';

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

