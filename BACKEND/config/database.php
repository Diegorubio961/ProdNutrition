<?php

namespace Config;
use Core\Env;

$driver = Env::get('DB_CONNECTION') ?? 'mysql';
$dbName = Env::get('DB_NAME'); // Ej: "grape"

if ($driver === 'sqlite') {
    // 1. Limpiamos el nombre por si acaso (quitamos rutas extrañas)
    $filename = basename($dbName);

    // 2. Si el usuario NO puso ".sqlite" al final, se lo ponemos nosotros
    if (!str_ends_with($filename, '.sqlite')) {
        $filename .= '.sqlite';
    }

    // 3. Ruta final forzada: database/grape.sqlite
    $sqlitePath = BASE_PATH . '/database/' . $filename;

    return [
        'driver' => 'sqlite',
        'dsn'    => 'sqlite:' . $sqlitePath,
        'user'   => null,
        'pass'   => null,
    ];

} else {
    // Para MySQL usamos el nombre tal cual ("grape")
    return [
        'driver' => 'mysql',
        'dsn'    => 'mysql:host=' . Env::get('DB_HOST') . ';dbname=' . $dbName . ';charset=utf8mb4',
        'user'   => Env::get('DB_USER'),
        'pass'   => Env::get('DB_PASS'),
    ];
}