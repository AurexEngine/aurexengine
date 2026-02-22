<?php

namespace AurexEngine\Http;

use AurexEngine\Routing\Dispatcher;
use AurexEngine\Foundation\Exceptions\Handler;

class Kernel
{
    /** @var array<int, callable|string> */
    protected array $middleware = [];
    protected array $middlewareInstances = [];

    /** @var array<int, class-string> */
    protected array $middlewarePriority = [];

    public function __construct(
        protected Dispatcher $dispatcher,
        protected Handler $exceptions
    ) {
    }

    public function middleware(array $middleware): self
    {
        $this->middleware = array_values($middleware);
        return $this;
    }

    public function handle(Request $request): Response
    {
        $this->middlewareInstances = [];

        try {
            return $this->dispatcher->dispatch(
                $request,
                $this->middleware,
                function (object $mw): void {
                    $this->middlewareInstances[] = $mw;
                }
            );
        } catch (\Throwable $e) {
            return $this->exceptions->render($request, $e);
        }
    }

    public function terminate(Request $request, Response $response): void
    {
        foreach ($this->middlewareInstances as $middleware) {
            if (method_exists($middleware, 'terminate')) {
                $middleware->terminate($request, $response);
            }
        }
    }

    public function middlewarePriority(array $priority): self
    {
        $this->middlewarePriority = array_values($priority);
        return $this;
    }
}
