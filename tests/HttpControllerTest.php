<?php

namespace Monolyth\Test;

use PHPUnit_Framework_TestCase;
use Monolyth\HttpController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\SapiEmitter;
use League\Pipeline\Pipeline;

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
        $front = new HttpController;
        $front->pipe(function ($request) {
            $response = new HtmlResponse('Hello world');
            $emitter = new SapiEmitter;
            $emitter->emit($response);
        });
        $front->run();
    }
}

