<?php

use Monolyth\Frontal\Controller;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * Tests for Monolyth's HTTP kernel.
 */
return function () : Generator {
    /**
     * Running the Http Controller should emit a response.
     */
    yield function () {
        ob_start();
        $_SERVER['REQUEST_URI'] = '/';
        $front = new Controller;
        $front->pipe(function ($request) {
            return new HtmlResponse('Hello world');
        });
        $front->run();
        assert(ob_get_clean() == 'Hello world');
    };
};

