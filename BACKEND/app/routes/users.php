<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/users', 'middleware' => []], function($router) {
    $router->post('/create', [
        'action' => ['App\Controllers\UserController', 'index']
    ]);
});
