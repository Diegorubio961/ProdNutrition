<?php

namespace Core;

class Env
{
    public static function load(string $path = BASE_PATH . '/.env'): void
    {
        if (!file_exists($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (str_starts_with(trim($line), '#')) continue;

            [$name, $value] = explode('=', $line, 2);
            $name  = trim($name);
            $value = trim($value);

            // Remover comillas si existen
            $value = trim($value, '"\'');

            // Guardar en $_ENV y como variable de entorno
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
