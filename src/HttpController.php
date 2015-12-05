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
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\SoapResponseHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Handler\PlainTextHandler;

class HttpController
{
    protected $pipeline;

    public function __construct(Pipeline $pipeline = null)
    {
        $this->pipeline = new PipelineBuilder;
        if (isset($pipeline)) {
            $this->pipeline->add($pipeline);
        }
        $whoops = new Run;
        $whoops->pushHandler(new PrettyPageHandler);
        $whoops->pushHandler(new JsonResponseHandler);
        $whoops->pushHandler(new SoapResponseHandler);
        $whoops->pushHandler(new XmlResponseHandler);
        $whoops->pushHandler(new PlainTextHandler);
        $whoops->register();
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

