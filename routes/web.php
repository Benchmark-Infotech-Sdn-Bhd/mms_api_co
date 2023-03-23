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
    /**
     * Routes for Agent.
     */
    $router->group(['prefix' => 'agent'], function () use ($router) {
        $router->post('create', 'V1\AgentController@create');
        $router->put('update', 'V1\AgentController@update');
        $router->post('delete', 'V1\AgentController@delete');
        $router->post('retrieve', 'V1\AgentController@retrieve');
        $router->get('retrieveAll', 'V1\AgentController@retrieveAll');
        $router->post('retrieveByCountry', 'V1\AgentController@retrieveByCountry');
    });
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
        $router->post('deleteAttachment', 'V1\VendorController@deleteAttachment');
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
        $router->post('deleteAttachment', 'V1\AccommodationController@deleteAttachment');
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
