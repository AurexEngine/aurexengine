<?php

namespace AurexEngine\Support;

interface Logger
{
    public function info(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
}