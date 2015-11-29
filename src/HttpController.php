<?php

namespace Monolyth;

use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\SapiEmitter;
use League\Pipeline\Pipeline;
use League\Pipeline\PipelineBuilder;
use Exception;

class HttpController
{
    protected $pipeline;

    public function __construct(Pipeline $pipeline = null)
    {
        $this->pipeline = new PipelineBuilder;
        if (isset($pipeline)) {
            $this->pipeline->add($pipeline);
        }
    }

    public function pipe(callable $stage)
    {
        $this->pipeline->add(new Stage($stage));
        return $this;
    }

    public function run()
    {
        $this->pipeline->build()
            ->pipe(new Stage(function (ResponseInterface $response) {
                $emitter = new SapiEmitter;
                return $emitter->emit($response);
            }))
            ->process(ServerRequestFactory::fromGlobals());
    }
}

