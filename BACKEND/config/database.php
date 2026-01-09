<?php

namespace Config;
use Core\Env;

return [
    'dsn'  => 'mysql:host=' . Env::get('DB_HOST') . ';dbname=' . Env::get('DB_NAME') . ';charset=utf8mb4',
    'user' => Env::get('DB_USER'),
    'pass' => Env::get('DB_PASS'),
];
