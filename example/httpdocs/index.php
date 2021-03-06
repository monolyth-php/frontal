<?php

namespace Monolyth\Frontal;

/**
 * Example index.php you can extend on.
 * Be sure to change paths to reflect your server environment.
 */

use Disclosure\Container;
use Cesession\Session;
use Cesession\Handler;
use League\Pipeline\Pipeline;
use Zend\Diactoros\Response\HtmlResponse;

/** Require and setup the Composer autoloader. */
$autoloader = require_once '../../vendor/autoload.php';

/** @see Monolyth\Plumber\Utf8 */
if (class_exists('Monolyth\Plumber\Utf8')) {
    Monolyth\Plumber\Utf8::handle();
}
/** @see Monolyth\Plumber\Proxy */
if (class_exists('Monolyth\Plumber\Proxy')) {
    Monolyth\Plumber\Proxy::handle();
}

$pipeline = new Pipeline;
$controller = new Controller;

/**
 * This is just an example for the default welcome page. Real projects
 * should use a router of some sort.
 */
$controller->pipe(function ($request) {
    return new HtmlResponse(file_get_contents('../src/template.html'));
});

try {
    $controller->run();
} catch (Throwable $e) {
    // You should do something useful here...
    throw $e;
} catch (Exception $e) {
    // For PHP5.* compatibility. Should contain the same logic as the previous
    // handler block.
    throw $e;
}

