<?php

namespace Monolyth\Http;

use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Response\EmptyResponse;
use League\Pipeline\Pipeline;
use League\Pipeline\PipelineBuilder;
use Whoops\Run;
use Whoops\Handler\HandlerInterface;
use Monolyth\Stage;

class Controller
{
    protected $pipeline;
    private $whoops;
    private $caught;
    private $errors = [];

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

    public function run(callable $errorHandler = null)
    {
        $request = ServerRequestFactory::fromGlobals();
        try {
            $this->pipeline->build()
                ->pipe(new Stage(function (ResponseInterface $response = null) {
                    $emitter = new SapiEmitter;
                    if (is_null($response)) {
                        $response = new EmptyResponse(404);
                    }
                    if (session_status() == PHP_SESSION_ACTIVE) {
                        session_write_close();
                    }
                    return $emitter->emit($response);
                }))
                ->process($request);
        } catch (Exception $e) {
            if (isset($errorHandler)) {
                $response = $errorHandler($e, $request);
                $emitter = new SapiEmitter;
                return $emitter->emit($response);
            } else {
                throw $e;
            }
        }
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

