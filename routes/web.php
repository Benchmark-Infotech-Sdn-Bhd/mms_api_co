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
    /**
     * Routes for EmbassyAttestationFileCosting.
     */
    $router->group(['prefix' => 'embassyAttestationFile'], function () use ($router) {
        $router->post('create', 'V1\EmbassyAttestationFileCostingController@create');
        $router->put('update', 'V1\EmbassyAttestationFileCostingController@update');
        $router->post('delete', 'V1\EmbassyAttestationFileCostingController@delete');
        $router->post('retrieveByCountry', 'V1\EmbassyAttestationFileCostingController@retrieveByCountry');
    });
    /**
     * Routes for Sectors.
     */
    $router->group(['prefix' => 'sector'], function () use ($router) {
        $router->post('create', 'V1\SectorsController@create');
        $router->put('update', 'V1\SectorsController@update');
        $router->post('delete', 'V1\SectorsController@delete');
        $router->post('retrieve', 'V1\SectorsController@retrieve');
        $router->get('retrieveAll', 'V1\SectorsController@retrieveAll');
    });
    /**
     * Routes for DocumentChecklist.
     */
    $router->group(['prefix' => 'documentChecklist'], function () use ($router) {
        $router->post('create', 'V1\DocumentChecklistController@create');
        $router->put('update', 'V1\DocumentChecklistController@update');
        $router->post('delete', 'V1\DocumentChecklistController@delete');
        $router->post('retrieveBySector', 'V1\DocumentChecklistController@retrieveBySector');
    });
});
