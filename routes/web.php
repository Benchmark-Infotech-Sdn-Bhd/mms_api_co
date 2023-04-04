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
        /**
         * Routes for Users.
         */
        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->post('register', 'V1\AuthController@register');
            $router->get('logout', 'V1\AuthController@logout');
            $router->get('refresh', 'V1\AuthController@refresh');
        });
         /**
         * Routes for Roles.
         */
        $router->group(['prefix' => 'role'], function () use ($router) {
            $router->post('list', 'V1\RolesController@list');
            $router->post('show', 'V1\RolesController@show');
            $router->post('create', 'V1\RolesController@create');
            $router->post('update', 'V1\RolesController@update');
            $router->post('delete', 'V1\RolesController@delete');
            $router->post('dropDown', 'V1\RolesController@dropDown');
        });
        /**
         * Routes for Modules.
         */
        $router->group(['prefix' => 'module'], function () use ($router) {
            $router->post('dropDown', 'V1\ModulesController@dropDown');
        });
        /**
         * Routes for Access Management.
         */
        $router->group(['prefix' => 'accessManagement'], function () use ($router) {
            $router->post('list', 'V1\AccessManagementController@list');
            $router->post('create', 'V1\AccessManagementController@create');
            $router->post('update', 'V1\AccessManagementController@update');
        });
        /**
         * Routes for Services.
         */
        $router->group(['prefix' => 'service'], function () use ($router) {
            $router->post('list', 'V1\ServicesController@list');
            $router->post('dropDown', 'V1\ServicesController@dropDown');
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
        $router->get('dropdown', 'V1\CountriesController@dropdown');
        $router->put('updateCostingStatus', 'V1\CountriesController@updateCostingStatus');
        $router->post('list', 'V1\CountriesController@list');
    });
    /**
     * Routes for EmbassyAttestationFileCosting.
     */
    $router->group(['prefix' => 'embassyAttestationFile'], function () use ($router) {
        $router->post('create', 'V1\EmbassyAttestationFileCostingController@create');
        $router->put('update', 'V1\EmbassyAttestationFileCostingController@update');
        $router->post('delete', 'V1\EmbassyAttestationFileCostingController@delete');
        $router->post('retrieve', 'V1\EmbassyAttestationFileCostingController@retrieve');
        $router->get('retrieveAll', 'V1\EmbassyAttestationFileCostingController@retrieveAll');
        $router->post('list', 'V1\EmbassyAttestationFileCostingController@list');
    });
    /**
     * Routes for Sectors.
     */
    $router->group(['prefix' => 'sector'], function () use ($router) {
        $router->post('create', 'V1\SectorsController@create');
        $router->put('update', 'V1\SectorsController@update');
        $router->post('delete', 'V1\SectorsController@delete');
        $router->post('retrieve', 'V1\SectorsController@retrieve');
        $router->get('dropdown', 'V1\SectorsController@dropdown');
        $router->put('updateChecklistStatus', 'V1\SectorsController@updateChecklistStatus');
        $router->post('list', 'V1\SectorsController@list');
    });
    /**
     * Routes for DocumentChecklist.
     */
    $router->group(['prefix' => 'documentChecklist'], function () use ($router) {
        $router->post('create', 'V1\DocumentChecklistController@create');
        $router->put('update', 'V1\DocumentChecklistController@update');
        $router->post('delete', 'V1\DocumentChecklistController@delete');
        $router->post('retrieve', 'V1\DocumentChecklistController@retrieve');
        $router->get('retrieveAll', 'V1\DocumentChecklistController@retrieveAll');
        $router->post('list', 'V1\DocumentChecklistController@list');
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
        $router->post('list', 'V1\AgentController@list');
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
        $router->post('filter', 'V1\VendorController@filter');
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

    /**
     * Routes for Branch.
     */
    $router->group(['prefix' => 'branch'], function () use ($router) {
        $router->post('create', 'V1\BranchController@create');
        $router->put('update', 'V1\BranchController@update');
        $router->post('delete', 'V1\BranchController@delete');
        $router->post('retrieve', 'V1\BranchController@retrieve');
        $router->get('retrieveAll', 'V1\BranchController@retrieveAll');
        $router->post('search', 'V1\BranchController@search');
    });
    /**
     * Routes for Employees.
     */
    $router->group(['prefix' => 'employee'], function () use ($router) {
        $router->post('create', 'V1\EmployeeController@create');
        $router->put('update', 'V1\EmployeeController@update');
        $router->post('delete', 'V1\EmployeeController@delete');
        $router->post('retrieve', 'V1\EmployeeController@retrieve');
        $router->get('retrieveAll', 'V1\EmployeeController@retrieveAll');
        $router->put('updateStatus', 'V1\EmployeeController@updateStatus');
        $router->post('list', 'V1\EmployeeController@list');
    });
});
