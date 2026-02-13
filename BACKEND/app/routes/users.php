<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/users', 'middleware' => []], function($router) {
    $router->post('/create/nutritionist', [
        'action' => ['App\Controllers\NutritionistController', 'createNutritionist']
    ]);
    $router->post('/delete/nutritionist', [
        'action' => ['App\Controllers\NutritionistController', 'deleteNutritionist']
    ]);
    $router->post('/read/nutritionists', [
        'action' => ['App\Controllers\NutritionistController', 'readNutritionist']
    ]);
    $router->post('/update/nutritionist', [
        'action' => ['App\Controllers\NutritionistController', 'updateNutritionist']
    ]);
    $router->post('/create/patient', [
        'action' => ['App\Controllers\patientController', 'createPatient']
    ]);
    $router->post('/update/patient', [
        'action' => ['App\Controllers\patientController', 'updatePatient']
    ]);
    $router->post('/delete/patient', [
        'action' => ['App\Controllers\patientController', 'deletePatient']
    ]);
    $router->post('/read/patient', [
        'action' => ['App\Controllers\patientController', 'readPatients']
    ]);
});
