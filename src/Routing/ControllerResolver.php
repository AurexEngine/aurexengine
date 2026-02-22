<?php

namespace AurexEngine\Routing;

use AurexEngine\Container\Container;
use AurexEngine\Http\Request;
use AurexEngine\Http\Response;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class ControllerResolver
{
    public function __construct(protected Container $container) {}

    /**
     * @param array<string, mixed> $routeParams
     */
    public function call(string $action, Request $request, array $routeParams = []): Response
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

        // ✅ Build arguments automatically
        $args = $this->resolveMethodArguments($controller, $method, $request, $routeParams);

        $result = $controller->{$method}(...$args);

        return $result instanceof Response
            ? $result
            : new Response((string) $result, 200);
    }

    /**
     * @param object $controller
     * @param array<string, mixed> $routeParams
     * @return array<int, mixed>
     */
    protected function resolveMethodArguments(object $controller, string $method, Request $request, array $routeParams): array
    {
        $ref = new ReflectionMethod($controller, $method);

        $args = [];
        foreach ($ref->getParameters() as $param) {
            $args[] = $this->resolveParameter($param, $request, $routeParams);
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $routeParams
     */
    protected function resolveParameter(ReflectionParameter $param, Request $request, array $routeParams): mixed
    {
        $type = $param->getType();
        $name = $param->getName();

        // 1) Request injection by type-hint
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $class = $type->getName();

            if (is_a($class, Request::class, true)) {
                return $request;
            }

            // 2) Class dependency -> container
            return $this->container->make($class);
        }

        // 3) Route param by name (e.g. $id)
        if (array_key_exists($name, $routeParams)) {
            $value = $routeParams[$name];

            // scalar cast if typed
            if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
                return $this->castScalar($value, $type->getName());
            }

            return $value;
        }

        // 4) default value
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        // 5) cannot resolve
        throw new \RuntimeException("Unable to resolve parameter \${$name} for controller method.");
    }

    protected function castScalar(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'string' => (string) $value,
            default => $value,
        };
    }
}