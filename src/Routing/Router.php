<?php

namespace AurexEngine\Routing;

use AurexEngine\Http\Request;

class Router
{
    /** @var Route[] */
    protected array $routes = [];
    protected ?string $controllerNamespace = null;

    public function setControllerNamespace(string $namespace): self
    {
        $this->controllerNamespace = trim($namespace, '\\');
        return $this;
    }

    public function getControllerNamespace(): ?string
    {
        return $this->controllerNamespace;
    }

    public function get(string $path, mixed $handler): Route
    {
        $route = new Route('GET', $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function post(string $path, mixed $handler): Route
    {
        $route = new Route('POST', $path, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function match(Request $request): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->method !== $request->method) {
                continue;
            }

            $params = [];
            if ($this->pathMatches($route->path, $request->path, $params)) {
                $request->params = $params; // attach extracted params
                return $route;
            }
        }

        return null;
    }

    /**
     * Supports patterns like:
     * /users/{id}
     * /posts/{postId}/comments/{commentId}
     */
    protected function pathMatches(string $routePath, string $reqPath, array &$params): bool
    {
        $params = [];

        $routeParts = explode('/', trim($routePath, '/'));
        $reqParts   = explode('/', trim($reqPath, '/'));

        if (count($routeParts) !== count($reqParts)) {
            return false;
        }

        foreach ($routeParts as $i => $part) {
            $val = $reqParts[$i] ?? '';

            // {param}
            $isParam = strlen($part) >= 2 && $part[0] === '{' && $part[strlen($part) - 1] === '}';

            if ($isParam) {
                $key = trim($part, "{} \t\n\r\0\x0B");
                if ($key === '') return false;

                $params[$key] = $val;
                continue;
            }

            // literal match
            if ($part !== $val) {
                return false;
            }
        }

        return true;
    }
}
