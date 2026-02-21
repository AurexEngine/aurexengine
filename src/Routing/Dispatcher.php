<?php

namespace AurexEngine\Routing;

use AurexEngine\Container\Container;
use AurexEngine\Http\Request;
use AurexEngine\Http\Response;
use AurexEngine\Http\Middleware\Pipeline;

class Dispatcher
{
    public function __construct(
        protected Router $router,
        protected Container $container
    ) {}

    /**
     * @param array<int, callable|string> $globalMiddleware
     */
    public function dispatch(Request $request, array $globalMiddleware = [], ?callable $collector = null, array $priority = []): Response
    {
        $route = $this->router->match($request);

        if (!$route) {
            return new Response('404 Not Found', 404);
        }

        $pipeline = new Pipeline($this->container);
        $pipeline->setMiddlewareCollector($collector);

        $middleware = array_merge($globalMiddleware, $route->middleware);

        if (!empty($priority)) {
            $middleware = $this->sortMiddlewareByPriority($middleware, $priority);
        }

        return $pipeline
            ->send($request)
            ->through($middleware)
            ->then(function (Request $request) use ($route): Response {
                $handler = $route->handler;

                if (is_string($handler)) {
                    // If handler has no namespace, prepend router namespace
                    if (strpos($handler, '\\') === false) {
                        $ns = $this->router->getControllerNamespace();
                        if ($ns) {
                            $handler = $ns . '\\' . $handler;
                        }
                    }

                    $resolver = new ControllerResolver($this->container);
                    return $resolver->call($handler, $request);
                }

                // callable handler
                if (is_callable($handler)) {
                    $result = $handler($request);

                    if ($result instanceof Response) {
                        return $result;
                    }

                    return new Response((string) $result, 200);
                }

                return new Response('Invalid route handler', 500);
            });
    }

    /**
     * @param array<int, callable|string> $middleware
     * @param array<int, class-string> $priority
     * @return array<int, callable|string>
     */
    private function sortMiddlewareByPriority(array $middleware, array $priority): array
    {
        // Only sort class-string middleware. Callables stay in place relative to each other.
        $priorityIndex = array_flip($priority);

        usort($middleware, function ($a, $b) use ($priorityIndex) {
            $aIsClass = is_string($a);
            $bIsClass = is_string($b);

            // Keep callables stable relative to classes (classes can move around them)
            if (!$aIsClass && !$bIsClass) return 0;
            if (!$aIsClass && $bIsClass)  return 1;
            if ($aIsClass && !$bIsClass)  return -1;

            $ai = $priorityIndex[$a] ?? PHP_INT_MAX;
            $bi = $priorityIndex[$b] ?? PHP_INT_MAX;

            return $ai <=> $bi;
        });

        return $middleware;
    }
}
