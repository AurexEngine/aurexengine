<?php

namespace Tests\Unit;

use AurexEngine\Container\Container;
use Tests\TestCase;

class ContainerTest extends TestCase
{
    public function test_it_binds_and_resolves_instance(): void
    {
        $c = new Container();

        $obj = new \stdClass();
        $c->instance('foo', $obj);

        $this->assertSame($obj, $c->make('foo'));
    }

    public function test_it_resolves_singleton(): void
    {
        $c = new Container();

        $c->singleton('bar', fn () => new \stdClass());

        $a = $c->make('bar');
        $b = $c->make('bar');

        $this->assertSame($a, $b);
    }

    public function test_it_autowires_constructor_dependencies(): void
    {
        $c = new Container();

        $obj = $c->make(Foo::class);

        $this->assertInstanceOf(Foo::class, $obj);
        $this->assertInstanceOf(Bar::class, $obj->bar);
    }
}

class Bar {}

class Foo
{
    public function __construct(public Bar $bar) {}
}