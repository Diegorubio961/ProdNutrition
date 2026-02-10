<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/appointments', 'middleware' => []], function($router) {
    $router->post('/create', [
        'action' => ['App\Controllers\AppointmentController', 'createAppointment']
    ]);
    $router->post('/read', [
        'action' => ['App\Controllers\AppointmentController', 'readAppointments']
    ]);
    $router->post('/delete', [
        'action' => ['App\Controllers\AppointmentController', 'deleteAppointment']
    ]);
    $router->post('/update', [
        'action' => ['App\Controllers\AppointmentController', 'updateAppointment']
    ]);
});
