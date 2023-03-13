<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'Welcome';//$router->app->version();
});

$router->group(['prefix' => 'api/v1', 'middleware' => ['dbSelection']], function () use ($router) {
    $router->post('create', 'V1\MaintainMastersController@create');
    $router->post('login', 'V1\AuthController@login');
    $router->group(['middleware' => ['jwt.verify']], function () use ($router) {
        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->post('register', 'V1\AuthController@register');
            $router->get('logout', 'V1\AuthController@logout');
        });
    });
});
