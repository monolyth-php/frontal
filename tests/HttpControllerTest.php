<?php

namespace Monolyth\Test;

use PHPUnit_Framework_TestCase;
use Monolyth\Http\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class HttpControllerTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        HeaderStack::reset();
        $this->callback = function ($request, $response, $done) {};
        $this->request = $this->getMock('Psr\Http\Message\ServerRequestInterface');
        $this->response = $this->getMock('Psr\Http\Message\ResponseInterface');
    }

    public function tearDown()
    {
        HeaderStack::reset();
    }

    public function testHttpController()
    {
        $this->expectOutputString('Hello world');
        $_SERVER['REQUEST_URI'] = '/';
        $front = new Controller;
        $front->pipe(function ($request) {
            return new HtmlResponse('Hello world');
        });
        $front->run();
    }
}

