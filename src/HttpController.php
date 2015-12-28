<?php

namespace Monolyth;

use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response\EmptyResponse;
use League\Pipeline\Pipeline;
use League\Pipeline\PipelineBuilder;
use Exception;
use Whoops\Run;
use Whoops\Handler\HandlerInterface;

class HttpController
{
    protected $pipeline;
    private $whoops;

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
            ->pipe(new Stage(function (ResponseInterface $response = null) {
                $emitter = new SapiEmitter;
                if (is_null($response)) {
                    $response = new EmptyResponse(404);
                }
                return $emitter->emit($response);
            }))
            ->process(ServerRequestFactory::fromGlobals());
    }

    public function whoops(HandlerInterface $handler)
    {
        if (!isset($this->whoops)) {
            $this->whoops = new Run;
        }
        $this->whoops->pushHandler($handler);
        $this->whoops->register();
    }
}

