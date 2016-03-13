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
use Zend\Diactoros\Response\HtmlResponse;

/** Require and setup the Composer autoloader. */
$autoloader = require_once '../../vendor/autoload.php';

/** @see Monolyth\Utilities::utf8 */
Utilities::utf8();
/** @see Monolyth\Utilities::proxy */
Utilities::proxy();

$pipeline = new Pipeline;
$controller = new HttpController;

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

