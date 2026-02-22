<?php

namespace AurexEngine\Support;

class FileLogger implements Logger
{
    public function __construct(protected string $path) {}

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    protected function write(string $level, string $message, array $context): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $time = date('Y-m-d H:i:s');
        $ctx  = $context ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $line = "[{$time}] {$level}: {$message}" . ($ctx ? " {$ctx}" : "") . PHP_EOL;

        file_put_contents($this->path, $line, FILE_APPEND);
    }
}