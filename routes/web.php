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
     * Routes for Vendors.
     */
    $router->group(['prefix' => 'vendor'], function () use ($router) {
        $router->post('create', 'V1\VendorController@create');
        $router->post('update', 'V1\VendorController@update');
        $router->post('delete', 'V1\VendorController@delete');
        $router->post('retrieve', 'V1\VendorController@retrieve');
        $router->get('retrieveAll', 'V1\VendorController@retrieveAll');
        $router->post('search', 'V1\VendorController@search');
    });

    /**
     * Routes for FOMEMA Clinics.
     */
    $router->group(['prefix' => 'fomemaClinics'], function () use ($router) {
        $router->post('create', 'V1\FomemaClinicsController@create');
        $router->put('update', 'V1\FomemaClinicsController@update');
        $router->post('delete', 'V1\FomemaClinicsController@delete');
        $router->post('retrieve', 'V1\FomemaClinicsController@retrieve');
        $router->get('retrieveAll', 'V1\FomemaClinicsController@retrieveAll');
        $router->post('search', 'V1\FomemaClinicsController@search');
    });

    /**
     * Routes for Fee Registration.
     */
    $router->group(['prefix' => 'feeRegistration'], function () use ($router) {
        $router->post('create', 'V1\FeeRegistrationController@create');
        $router->put('update', 'V1\FeeRegistrationController@update');
        $router->post('delete', 'V1\FeeRegistrationController@delete');
        $router->post('retrieve', 'V1\FeeRegistrationController@retrieve');
        $router->get('retrieveAll', 'V1\FeeRegistrationController@retrieveAll');
        $router->post('search', 'V1\FeeRegistrationController@search');
    });

    /**
     * Routes for Accommodation.
     */
    $router->group(['prefix' => 'accommodation'], function () use ($router) {
        $router->post('create', 'V1\AccommodationController@create');
        $router->post('update', 'V1\AccommodationController@update');
        $router->post('delete', 'V1\AccommodationController@delete');
        $router->post('retrieve', 'V1\AccommodationController@retrieve');
        $router->get('retrieveAll', 'V1\AccommodationController@retrieveAll');
        $router->post('search', 'V1\AccommodationController@search');
    });

    /**
     * Routes for Insurance.
     */
    $router->group(['prefix' => 'insurance'], function () use ($router) {
        $router->post('create', 'V1\InsuranceController@create');
        $router->put('update', 'V1\InsuranceController@update');
        $router->post('delete', 'V1\InsuranceController@delete');
        $router->post('retrieve', 'V1\InsuranceController@retrieve');
        $router->get('retrieveAll', 'V1\InsuranceController@retrieveAll');
        $router->post('search', 'V1\InsuranceController@search');
    });

    /**
     * Routes for Transportation.
     */
    $router->group(['prefix' => 'transportation'], function () use ($router) {
        $router->post('create', 'V1\TransportationController@create');
        $router->put('update', 'V1\TransportationController@update');
        $router->post('delete', 'V1\TransportationController@delete');
        $router->post('retrieve', 'V1\TransportationController@retrieve');
        $router->get('retrieveAll', 'V1\TransportationController@retrieveAll');
        $router->post('search', 'V1\TransportationController@search');
    });
});