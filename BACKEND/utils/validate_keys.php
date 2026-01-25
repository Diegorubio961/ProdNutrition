<?php

namespace Utils;

class validate_keys
{
    public static function validateTypes(array $payload, array $schema): array
    {
        $errors = [];

        foreach ($schema as $key => $type) {
            if (!array_key_exists($key, $payload)) {
                $errors[$key] = "missing";
                continue;
            }

            $value = $payload[$key];

            if (!self::isType($value, $type)) {
                $errors[$key] = "type_{$type}";
            }
        }

        return [
            'ok' => empty($errors),
            'errors' => $errors
        ];
    }

    private static function isType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'int' => is_int($value),
            'float' => is_float($value) || is_int($value),
            'bool' => is_bool($value),
            'array' => is_array($value),
            'object' => is_array($value),
            default => false
        };
    }
}
