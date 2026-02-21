<?php

namespace AurexEngine\Routing;

use AurexEngine\Container\Container;
use AurexEngine\Http\Request;
use AurexEngine\Http\Response;

class ControllerResolver
{
    public function __construct(protected Container $container) {}

    public function call(string $action, Request $request): Response
    {
        if (strpos($action, '@') === false) {
            return new Response("Invalid controller action [$action]. Use Class@method.", 500);
        }

        [$class, $method] = explode('@', $action, 2);

        if (!class_exists($class)) {
            return new Response("Controller [$class] not found.", 500);
        }

        $controller = $this->container->make($class);

        if (!method_exists($controller, $method)) {
            return new Response("Method [$method] not found on controller [$class].", 500);
        }

        $result = $controller->$method($request);

        return $result instanceof Response
            ? $result
            : new Response((string) $result, 200);
    }
}