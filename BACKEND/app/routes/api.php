<?php

/** @var \Core\Router $router */

$router->group(['prefix' => '/api', 'middleware' => []], function($router) {
    require_once __DIR__ . '/api/auth.php';
});
