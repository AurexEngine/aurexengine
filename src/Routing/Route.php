<?php

namespace AurexEngine\Routing;

class Route
{
    /** @var array<int, callable|string> */
    public array $middleware = [];

    /** Full route name (after group prefix applied) */
    public ?string $name = null;

    /** Used internally to prefix name from groups */
    public string $namePrefix = '';

    /** @var callable|null */
    protected $onNamed = null;

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

    /**
     * Set route name (supports group name prefix)
     */
    public function name(string $name): self
    {
        $this->name = $this->namePrefix . $name;

        if (is_callable($this->onNamed)) {
            ($this->onNamed)($this);
        }

        return $this;
    }

    /** @internal */
    public function onNamed(?callable $callback): void
    {
        $this->onNamed = $callback;
    }
}