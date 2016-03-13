<?php

namespace Monolyth\Test;

use Monolyth\Http\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

require_once __DIR__.'/../HeaderStack.php';

/**
 * Tests for Monolyth's HTTP kernel.
 */
class HttpControllerTest
{
    /**
     * Running the Http Controller should emit a response {?}.
     */
    public function runHttpController()
    {
        ob_start();
        $_SERVER['REQUEST_URI'] = '/';
        $front = new Controller;
        $front->pipe(function ($request) {
            return new HtmlResponse('Hello world');
        });
        $front->run();
        yield assert(ob_get_clean() == 'Hello world');
    }
}

