<?php

namespace Monolyth;

use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\SapiEmitter;
use League\Pipeline\Pipeline;
use Exception;

class HttpController
{
    protected $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline->pipe(function ($request) {
            $response = new HtmlResponse('Hello world');
            $emitter = new SapiEmitter;
            $emitter->emit($response);
        });
    }

    public function run()
    {
        $this->pipeline->process(ServerRequestFactory::fromGlobals());
    }
}

