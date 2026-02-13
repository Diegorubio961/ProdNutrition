<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/measure', 'middleware' => []], function($router) {

    $router->post('/read', [
        'action' => ['App\Controllers\MeasureController', 'read']
    ]);
    $router->post('/update', [
        'action' => ['App\Controllers\MeasureController', 'update']
    ]);
    $router->post('/delete', [
        'action' => ['App\Controllers\MeasureController', 'delete']
    ]);
});
