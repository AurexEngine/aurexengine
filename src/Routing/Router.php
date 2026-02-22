<?php

namespace AurexEngine\Routing;

use AurexEngine\Http\Request;

class Router
{
    /** @var Route[] */
    protected array $routes = [];

    /** @var array<string, Route> */
    protected array $namedRoutes = [];

    protected ?string $controllerNamespace = null;

    /** Group stack for prefix/middleware/name prefix */
    protected array $groupStack = [];

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
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Route grouping:
     * $router->group(['prefix' => 'admin', 'middleware' => [...], 'as' => 'admin.'], function($router) {
     *     $router->get('/users', ...)->name('users.index');
     * });
     */
    public function group(array $attributes, callable $callback): void
    {
        $parent = $this->currentGroup();

        $group = [
            'prefix' => $this->joinPrefix($parent['prefix'] ?? '', $attributes['prefix'] ?? ''),
            'middleware' => array_merge($parent['middleware'] ?? [], $attributes['middleware'] ?? []),
            'as' => ($parent['as'] ?? '') . ($attributes['as'] ?? ''),
        ];

        $this->groupStack[] = $group;

        try {
            $callback($this);
        } finally {
            array_pop($this->groupStack);
        }
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
     * Get a route by its name.
     */
    public function getByName(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Generate a path from a route name (basic URL generator).
     */
    public function url(string $name, array $params = []): string
    {
        $route = $this->getByName($name);

        if (!$route) {
            throw new \RuntimeException("Route name [{$name}] not found.");
        }

        $path = $route->path;

        foreach ($params as $k => $v) {
            $path = str_replace('{' . $k . '}', (string) $v, $path);
        }

        return $path;
    }

    // ------------------------
    // Internal helpers
    // ------------------------

    protected function addRoute(string $method, string $path, mixed $handler): Route
    {
        $group = $this->currentGroup();

        $fullPath = $this->joinPath($group['prefix'] ?? '', $path);

        $route = new Route($method, $fullPath, $handler);

        // apply group middleware
        if (!empty($group['middleware'])) {
            $route->middleware($group['middleware']);
        }

        // apply group name prefix
        $route->namePrefix = $group['as'] ?? '';

        // register route name when set
        $route->onNamed(function (Route $r) {
            $this->namedRoutes[$r->name] = $r;
        });

        $this->routes[] = $route;
        return $route;
    }

    protected function currentGroup(): array
    {
        return $this->groupStack ? $this->groupStack[count($this->groupStack) - 1] : [];
    }

    protected function joinPrefix(string $base, string $prefix): string
    {
        $base = trim($base);
        $prefix = trim($prefix);

        $base = trim($base, '/');
        $prefix = trim($prefix, '/');

        if ($base === '') return $prefix;
        if ($prefix === '') return $base;

        return $base . '/' . $prefix;
    }

    protected function joinPath(string $prefix, string $path): string
    {
        $prefix = trim($prefix, '/');
        $path = '/' . ltrim($path, '/');

        if ($prefix === '') {
            return $path;
        }

        return '/' . $prefix . $path;
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