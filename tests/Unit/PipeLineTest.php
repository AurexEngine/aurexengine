<?php

namespace Tests\Unit;

use AurexEngine\Container\Container;
use AurexEngine\Http\Middleware\Pipeline;
use AurexEngine\Http\Request;
use AurexEngine\Http\Response;
use Tests\TestCase;

class PipelineTest extends TestCase
{
    public function test_it_runs_middleware_in_order(): void
    {
        $container = new Container();
        $pipeline = new Pipeline($container);

        $req = $this->makeRequest('GET', '/');

        $result = $pipeline->send($req)->through([
            FirstMiddleware::class,
            SecondMiddleware::class,
        ])->then(fn() => new Response('end'));

        $this->assertSame('first-second-end', $result->content);
    }

    public function test_it_calls_terminable_middleware_collector(): void
    {
        $container = new Container();
        $pipeline = new Pipeline($container);

        $seen = [];
        $pipeline->setMiddlewareCollector(function ($mw) use (&$seen) {
            $seen[] = get_class($mw);
        });

        $req = $this->makeRequest('GET', '/');

        $pipeline->send($req)->through([FirstMiddleware::class])->then(fn() => new Response('ok'));

        $this->assertSame([FirstMiddleware::class], $seen);
    }

    private function makeRequest(string $method, string $path): Request
    {
        return new Request($method, $path);
    }
}

class FirstMiddleware
{
    public function handle($request, $next): Response
    {
        $res = $next($request);
        $res->content = 'first-' . $res->content;
        return $res;
    }
}

class SecondMiddleware
{
    public function handle($request, $next): Response
    {
        $res = $next($request);
        $res->content = 'second-' . $res->content;
        return $res;
    }
}