<?php

namespace AurexEngine\Routing;

class Route
{
    /** @var array<int, callable|string> */
    public array $middleware = [];

    public function __construct(
        public string $method,
        public string $path,
        public mixed $handler
    ) {}

    public function middleware(array $middleware): self
    {
        $this->middleware = array_values($middleware);
        return $this;
    }
}