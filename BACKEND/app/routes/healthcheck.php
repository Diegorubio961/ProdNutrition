<?php
/** @var Router $router */

$router->get('/healthcheck', [
    'action'     => ['App\Controllers\HealthcheckController', 'health'],
    'middleware' => [
        'App\Middlewares\DumpMiddleware:prueba'
        ]
]);