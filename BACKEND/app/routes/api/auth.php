<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/auth', 'middleware' => []], function($router) {
    $router->post('/login', [
        'action' => ['App\Controllers\AuthController', 'login']
    ]);
});
