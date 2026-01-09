<?php

namespace Core;

class Request
{
    public string $method;
    public string $uri;
    public array $query;
    public array $body;
    public array $headers;

    public function __construct()
    {
        $this->method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri     = $_SERVER['REQUEST_URI'] ?? '/';
        $this->query   = $_GET ?? [];

        $this->headers = getallheaders();

        // Detectar si es JSON
        $input = file_get_contents('php://input');
        $this->body = json_decode($input, true) ?? $_POST ?? [];
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function header(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }
}
