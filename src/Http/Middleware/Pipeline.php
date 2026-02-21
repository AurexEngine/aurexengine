<?php

namespace AurexEngine\Http\Middleware;

use AurexEngine\Container\Container;
use AurexEngine\Http\Request;
use AurexEngine\Http\Response;

class Pipeline
{
    /** @var array<int, callable|string> */
    protected array $pipes = [];

    /** @var callable|null */
    protected $middlewareCollector = null;

    public function __construct(protected Container $container) {}

    public function send(Request $request): PipelineContext
    {
        return new PipelineContext($request, $this);
    }

    public function through(array $pipes): self
    {
        $this->pipes = array_values($pipes);
        return $this;
    }

    public function setMiddlewareCollector(?callable $collector): self
    {
        $this->middlewareCollector = $collector;
        return $this;
    }

    /** @internal */
    public function collectMiddleware(object $middleware): void
    {
        if (is_callable($this->middlewareCollector)) {
            ($this->middlewareCollector)($middleware);
        }
    }

    /** @internal */
    public function getPipes(): array
    {
        return $this->pipes;
    }

    /** @internal */
    public function getContainer(): Container
    {
        return $this->container;
    }
}

class PipelineContext
{
    public function __construct(
        protected Request $request,
        protected Pipeline $pipeline
    ) {}

    public function through(array $pipes): self
    {
        $this->pipeline->through($pipes);
        return $this;
    }

    /**
     * @param callable(Request): Response $destination
     */
    public function then(callable $destination): Response
    {
        $pipes = $this->pipeline->getPipes();

        $next = array_reduce(
            array_reverse($pipes),
            function ($stack, $pipe) {
                return function (Request $request) use ($stack, $pipe): Response {
                    // Pipe as callable middleware: function($request, $next)
                    if (is_callable($pipe)) {
                        $result = $pipe($request, $stack);
                        return $result instanceof Response ? $result : new Response((string) $result);
                    }

                    // Pipe as class-string with handle(Request $request, callable $next)
                    if (is_string($pipe) && class_exists($pipe)) {
                        $container = $this->pipeline->getContainer();
                        $mw = $container->make($pipe);

                        // Track for terminable middleware
                        $this->pipeline->collectMiddleware($mw);

                        if (!method_exists($mw, 'handle')) {
                            return $stack($request);
                        }

                        $result = $mw->handle($request, $stack);
                        return $result instanceof Response ? $result : new Response((string) $result);
                    }

                    return $stack($request);
                };
            },
            function (Request $request) use ($destination): Response {
                return $destination($request);
            }
        );

        return $next($this->request);
    }
}