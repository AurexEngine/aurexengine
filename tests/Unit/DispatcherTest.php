<?php

namespace Tests\Unit;

use AurexEngine\Container\Container;
use AurexEngine\Http\Request;
use AurexEngine\Routing\Dispatcher;
use AurexEngine\Routing\Router;
use AurexEngine\Http\Response;
use Tests\TestCase;

class DispatcherTest extends TestCase
{
    public function test_it_returns_404_when_no_route_matches(): void
    {
        $router = new Router();
        $container = new Container();
        $dispatcher = new Dispatcher($router, $container);

        $request = new Request('GET', '/missing');

        $response = $dispatcher->dispatch($request);

        $this->assertSame(404, $response->status);
    }

    public function test_it_dispatches_callable_route(): void
    {
        $router = new Router();
        $container = new Container();

        $router->get('/hello', fn () => 'world');

        $dispatcher = new Dispatcher($router, $container);

        $request = new Request('GET', '/hello');

        $response = $dispatcher->dispatch($request);

        $this->assertSame(200, $response->status);
        $this->assertSame('world', $response->content);
    }
}