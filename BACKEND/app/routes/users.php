<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/users', 'middleware' => []], function($router) {
    $router->post('/create/nutritionist', [
        'action' => ['App\Controllers\UserController', 'createNutritionist']
    ]);
    $router->post('/delete/nutritionist', [
        'action' => ['App\Controllers\UserController', 'deleteUser']
    ]);
    $router->post('/read/nutritionists', [
        'action' => ['App\Controllers\UserController', 'readUsers']
    ]);
    $router->post('/update/nutritionist', [
        'action' => ['App\Controllers\UserController', 'updateUser']
    ]);
});
