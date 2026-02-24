<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/history', 'middleware' => []], function($router) {
    $router->post('/create', [
        'action' => ['App\Controllers\HistoryController', 'create']
    ]);
    $router->post('/update', [
        'action' => ['App\Controllers\HistoryController', 'update']
    ]);
    $router->post('/delete', [
        'action' => ['App\Controllers\HistoryController', 'delete']
    ]);
    $router->post('/read', [
        'action' => ['App\Controllers\HistoryController', 'read']
    ]);
});
