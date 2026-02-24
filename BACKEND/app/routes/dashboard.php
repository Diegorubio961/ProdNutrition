<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/dashboard', 'middleware' => []], function($router) {
    $router->post('/nutritionist', [
        'action' => ['App\Controllers\DashboardController', 'getNutritionistDashboard']
    ]);
});
