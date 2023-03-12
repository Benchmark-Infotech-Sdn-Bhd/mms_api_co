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

    /**
     * Routes for Countries.
     */
    $router->group(['prefix' => 'country'], function () use ($router) {
        $router->post('create', 'V1\CountriesController@create');
        $router->put('update', 'V1\CountriesController@update');
        $router->post('delete', 'V1\CountriesController@delete');
        $router->post('retrieve', 'V1\CountriesController@retrieve');
        $router->get('retrieveAll', 'V1\CountriesController@retrieveAll');
    });
});
