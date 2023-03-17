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
    $router->group(['prefix' => 'role'], function () use ($router) {
        $router->post('list', 'V1\RolesController@list');
        $router->post('show', 'V1\RolesController@show');
        $router->post('dropDown', 'V1\RolesController@dropDown');
        $router->post('create', 'V1\RolesController@create');
        $router->post('update', 'V1\RolesController@update');
        $router->post('updateStatus', 'V1\RolesController@updateStatus');
    });
});
