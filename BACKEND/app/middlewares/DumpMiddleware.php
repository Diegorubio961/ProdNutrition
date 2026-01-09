<?php

namespace App\Middlewares;

class DumpMiddleware
{
    public function __construct(...$permissions)
    {
       echo "DumpMiddleware initialized with permissions: " . implode(', ', $permissions) . "\n";
    }

    public function handle(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'middleware' => 'DumpMiddleware ejecutado',
            'method'     => $_SERVER['REQUEST_METHOD'],
            'uri'        => $_SERVER['REQUEST_URI']
        ]);
        //exit; // ⚠️ Esto detiene la ejecución antes del controlador
    }
}
