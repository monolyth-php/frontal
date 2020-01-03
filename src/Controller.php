<?php

namespace Monolyth\Frontal;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use League\Pipeline\{ Pipeline, PipelineBuilder };

/**
 * Monolyth's front controller or "kernel".
 */
class Controller
{
    protected $pipeline;
    private $errors = [];

    /**
     * Constructor. You can optionally pass in a default pipeline to extend from
     * if your `index.php` needs additional logic/middleware.
     *
     * @param League\Pipeline\Pipeline $pipeline
     */
    public function __construct(Pipeline $pipeline = null)
    {
        $this->pipeline = new PipelineBuilder;
        if (isset($pipeline)) {
            $this->pipeline->add($pipeline);
        }
    }

    /**
     * Add a stage to the pipeline. The callable gets wrapped in
     * `Monolyth\Frontal\Stage` so its interface satisfies `league\pipeline`.
     *
     * HTTP pipe stages accept an argument (`$payload`) and should return either
     * an instance of `Psr\Http\Message\RequestInterface` (in which case the
     * next stage will be called) or `Psr\Http\Message\ResponseInterface` (in
     * which case the pipeline is terminated).
     *
     * @param callable $stage
     * @return self
     */
    public function pipe(callable $stage)
    {
        $this->pipeline->add(new Stage($stage));
        return $this;
    }

    /**
     * Run the HTTP kernel controller. This processes your pipeline and emits
     * the resulting response.
     *
     * @throws Error|Exception If any error or exception gets thrown during
     *  processing, your front controller should handle that gracefully (or not
     *  so gracefully if you're still developing).
     */
    public function run()
    {
        $request = ServerRequestFactory::fromGlobals();
        $this->pipeline->build()
            ->pipe(new Stage(function (ResponseInterface $response = null) {
                $emitter = new SapiEmitter;
                if (is_null($response)) {
                    throw new Exception(404);
                }
                return $emitter->emit($response);
            }))
            ->process($request);
    }
}

