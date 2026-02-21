<?php

namespace AurexEngine\Http;

class Response
{
    public function __construct(
        public string $content = '',
        public int $status = 200,
        public array $headers = ['content-type' => 'text/html; charset=UTF-8']
    ) {}

    public static function json(array $data, int $status = 200): static
    {
        return new static(
            content: json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            status: $status,
            headers: ['content-type' => 'application/json; charset=UTF-8']
        );
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }
        echo $this->content;
    }

    public static function redirect(string $to, int $status = 302, array $headers = []): static
    {
        $headers['Location'] = $to;

        return new static('', $status, $headers);
    }
}
