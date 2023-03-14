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
});

$router->group(['prefix' => 'api/v1'], function () use ($router) {
    Route::post('create_vendor', 'V1\VendorController@createVendor');
    Route::get('show_vendors', 'V1\VendorController@showVendors');
    Route::get('edit_vendors/{id}', 'V1\VendorController@editVendors');
    Route::post('update_vendors/{id}', 'V1\VendorController@updateVendors');
    Route::delete('delete_vendors/{id}', 'V1\VendorController@deleteVendors');
    Route::post('search_vendors', 'V1\VendorController@searchVendors');

    Route::post('create_clinic', 'V1\FomemaClinicsController@createFomemaClinics');
    Route::get('show_clinic', 'V1\FomemaClinicsController@showFomemaClinics');
    Route::get('edit_clinic/{id}', 'V1\FomemaClinicsController@editFomemaClinics');
    Route::put('update_clinic/{id}', 'V1\FomemaClinicsController@updateFomemaClinics');
    Route::delete('delete_clinic/{id}', 'V1\FomemaClinicsController@deleteFomemaClinics');
    Route::post('search_fomema_clinics', 'V1\FomemaClinicsController@searchFomemaClinics');

    Route::post('create_fee', 'V1\FeeRegistrationController@createFeeRegistration');
    Route::get('show_fee', 'V1\FeeRegistrationController@showFeeRegistration');
    Route::get('edit_fee/{id}', 'V1\FeeRegistrationController@editFeeRegistration');
    Route::put('update_fee/{id}', 'V1\FeeRegistrationController@updateFeeRegistration');
    Route::delete('delete_fee/{id}', 'V1\FeeRegistrationController@deleteFeeRegistration');
    Route::post('search_feeRegistration', 'V1\FeeRegistrationController@searchFeeRegistration');

    Route::post('create_accommodation', 'V1\AccommodationController@createAccommodation');
    Route::get('show_accommodation', 'V1\AccommodationController@showAccommodation');
    Route::get('edit_accommodation/{id}', 'V1\AccommodationController@editAccommodation');
    Route::post('update_accommodation/{id}', 'V1\AccommodationController@updateAccommodation');
    Route::delete('delete_accommodation/{id}', 'V1\AccommodationController@deleteAccommodation');
    Route::post('search_accommodation', 'V1\AccommodationController@searchAccommodation');

    Route::post('create_insurance', 'V1\InsuranceController@createInsurance');
    Route::get('show_insurance', 'V1\InsuranceController@showInsurance');
    Route::get('edit_insurance/{id}', 'V1\InsuranceController@editInsurance');
    Route::put('update_insurance/{id}', 'V1\InsuranceController@updateInsurance');
    Route::delete('delete_insurance/{id}', 'V1\InsuranceController@deleteInsurance');
    Route::post('search_insurance', 'V1\InsuranceController@searchInsurance');

    Route::post('create_transportation', 'V1\TransportationController@createTransportation');
    Route::get('show_transportation', 'V1\TransportationController@showTransportation');
    Route::get('edit_transportation/{id}', 'V1\TransportationController@editTransportation');
    Route::put('update_transportation/{id}', 'V1\TransportationController@updateTransportation');
    Route::delete('delete_transportation/{id}', 'V1\TransportationController@deleteTransportation');
    Route::post('search_transportation', 'V1\TransportationController@searchTransportation');

    Route::post('image_upload', 'V1\AccommodationController@uploadImage');
    
});