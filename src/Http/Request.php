<?php

namespace AurexEngine\Http;

class Request
{
    public string $method;
    public string $path;
    public array $query;
    public array $post;
    public array $server;
    public array $headers;
    public ?string $rawBody;
    public array $params = [];

    public function __construct(
        string $method,
        string $path,
        array $query = [],
        array $post = [],
        array $server = [],
        array $headers = [],
        ?string $rawBody = null,
        array $params = []
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->query = $query;
        $this->post = $post;
        $this->server = $server;
        $this->headers = $headers;
        $this->rawBody = $rawBody;
        $this->params = $params;
    }

    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (strpos($k, 'HTTP_') === 0) {
                $name = str_replace('_', '-', strtolower(substr($k, 5)));
                $headers[$name] = $v;
            }
        }

        $raw = file_get_contents('php://input');

        return new self(
            strtoupper($method),
            $path,
            $_GET,
            $_POST,
            $_SERVER,
            $headers,
            $raw === false ? null : $raw
        );
    }
}
