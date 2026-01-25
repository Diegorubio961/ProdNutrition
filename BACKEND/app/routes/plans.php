<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/plans', 'middleware' => []], function($router) {
    $router->post('/read', [
        'action' => ['App\Controllers\PlanController', 'readPlans']
    ]);
    $router->post('/delete', [
        'action' => ['App\Controllers\PlanController', 'deletePlan']
    ]);
    $router->post('/update', [
        'action' => ['App\Controllers\PlanController', 'updatePlan']
    ]);
    $router->post('/create', [
        'action' => ['App\Controllers\PlanController', 'createPlan']
    ]);
});
