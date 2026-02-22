<?php

namespace Tests\Unit;

use AurexEngine\Http\Request;
use AurexEngine\Routing\Router;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class RouterTest extends TestCase
{
    public function test_it_matches_literal_route(): void
    {
        $router = new Router();
        $router->get('/hello', fn() => 'ok');

        $req = $this->makeRequest('GET', '/hello');
        $route = $router->match($req);

        $this->assertNotNull($route);
        $this->assertSame('/hello', $route->path);
    }

    public function test_it_extracts_route_params(): void
    {
        $router = new Router();
        $router->get('/users/{id}', fn() => 'ok');

        $req = $this->makeRequest('GET', '/users/123');
        $route = $router->match($req);

        $this->assertNotNull($route);
        $this->assertSame('123', $req->params['id'] ?? null);
    }

    private function makeRequest(string $method, string $path): Request
    {
        return new Request($method, $path);
    }
}