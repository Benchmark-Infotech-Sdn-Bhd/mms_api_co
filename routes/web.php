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
    $router->post('forgotPassword', 'V1\AuthController@forgotPassword');
    $router->post('forgotPasswordUpdate', 'V1\AuthController@forgotPasswordUpdate');
    $router->group(['middleware' => ['jwt.verify']], function () use ($router) {  
        /**
         * Routes for Users.
         */
        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->post('register', 'V1\AuthController@register');
            $router->get('logout', 'V1\AuthController@logout');
            $router->post('refresh', 'V1\AuthController@refresh');
            $router->post('resetPassword', 'V1\UserController@resetPassword');
            $router->post('showUser', 'V1\UserController@showUser');
            $router->post('updateUser', 'V1\UserController@updateUser');
            $router->group(['middleware' => 'accessControl:14'], function () use ($router) {  
                $router->group(['prefix' => '', 'middleware' => ['permissions:14,View']], function () use ($router) {
                    $router->post('adminList', 'V1\UserController@adminList');
                    $router->post('adminShow', 'V1\UserController@adminShow');
                });                
                $router->group(['prefix' => '', 'middleware' => ['permissions:14,Edit']], function () use ($router) {
                    $router->post('adminUpdate', 'V1\UserController@adminUpdate');
                    $router->post('adminUpdateStatus', 'V1\UserController@adminUpdateStatus');
                });
            });
        });
        /**
         * Routes for Company.
         */
        $router->group(['middleware' => 'accessControl:15'], function () use ($router) {  
            $router->group(['prefix' => 'company'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:15,View']], function () use ($router) {
                    $router->post('list', 'V1\CompanyController@list');
                    $router->post('show', 'V1\CompanyController@show');   
                    $router->post('moduleList', 'V1\CompanyController@moduleList');                                    
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:15,Add']], function () use ($router) {
                    $router->post('create', 'V1\CompanyController@create');
                    $router->post('assignSubsidiary', 'V1\CompanyController@assignSubsidiary');
                    $router->post('assignModule', 'V1\CompanyController@assignModule');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:15,Edit']], function () use ($router) {
                    $router->post('update', 'V1\CompanyController@update');
                    $router->post('updateStatus', 'V1\CompanyController@updateStatus');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:15,Delete']], function () use ($router) {
                    $router->post('deleteAttachment', 'V1\CompanyController@deleteAttachment');
                });    
                $router->post('subsidiaryDropDown', 'V1\CompanyController@subsidiaryDropDown');
                $router->post('parentDropDown', 'V1\CompanyController@parentDropDown');  
                $router->post('subsidiaryDropdownBasedOnParent', 'V1\CompanyController@subsidiaryDropdownBasedOnParent');    
                $router->post('dropdown', 'V1\CompanyController@dropdown');       
            });
            
        });
        /**
         * Routes for Modules List - Using this API To Display the company list dropdown selection for All the Users types in Front-End.
         */
        $router->group(['prefix' => 'company'], function () use ($router) {
            $router->post('listUserCompany', 'V1\CompanyController@listUserCompany');
            $router->post('updateCompanyId', 'V1\CompanyController@updateCompanyId');
        });  
        /**
         * Routes for Modules.
         */
        $router->group(['prefix' => 'module'], function () use ($router) {
            $router->post('dropDown', 'V1\ModulesController@dropDown');
        });
        /**
         * Routes for Services.
         */
        $router->group(['prefix' => 'service'], function () use ($router) {
            $router->post('list', 'V1\ServicesController@list');
            $router->post('dropDown', 'V1\ServicesController@dropDown');
        });
        /**
        * Routes for Notification.
        */
        $router->group(['prefix' => 'notifications'], function () use ($router) {
            $router->post('count', 'V1\NotificationController@count');
            $router->post('list', 'V1\NotificationController@list');
            $router->post('updateReadStatus', 'V1\NotificationController@updateReadStatus');
            $router->post('renewalNotifications', 'V1\NotificationController@renewalNotifications');
        });
        /**
        * Routes for Audits.
        */
        $router->group(['prefix' => 'audits'], function () use ($router) {
            $router->post('list', 'V1\AuditsController@list');
        });
        /**
         * Routes for Dashboard
         */
        $router->group(['middleware' => 'accessControl:1'], function () use ($router) {  
            $router->group(['prefix' => 'dashboard'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:1,View']], function () use ($router) {
                    $router->post('list', 'V1\DashboardController@list');
                });
            });
        });
        /**
         * Routes for Maintain Masters
         */
        $router->group(['middleware' => 'accessControl:2'], function () use ($router) { 
            /**
             * Routes for Countries.
             */ 
            $router->group(['prefix' => 'country'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\CountriesController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\CountriesController@update');
                    $router->post('updateStatus', 'V1\CountriesController@updateStatus');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\CountriesController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\CountriesController@show');                   
                    $router->post('list', 'V1\CountriesController@list');                
                });
                $router->post('dropDown', 'V1\CountriesController@dropdown');
            });
            /**
             * Routes for EmbassyAttestationFileCosting.
             */
            $router->group(['prefix' => 'embassyAttestationFile'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\EmbassyAttestationFileCostingController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\EmbassyAttestationFileCostingController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\EmbassyAttestationFileCostingController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\EmbassyAttestationFileCostingController@show');
                    $router->post('list', 'V1\EmbassyAttestationFileCostingController@list');
                });
            });
            /**
             * Routes for Sectors.
             */
            $router->group(['prefix' => 'sector'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\SectorsController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\SectorsController@update');
                    $router->post('updateStatus', 'V1\SectorsController@updateStatus');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\SectorsController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\SectorsController@show');
                    $router->post('list', 'V1\SectorsController@list');
                });    
                $router->post('dropDown', 'V1\SectorsController@dropdown');            
            });
            /**
             * Routes for DocumentChecklist.
             */
            $router->group(['prefix' => 'documentChecklist'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\DocumentChecklistController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\DocumentChecklistController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\DocumentChecklistController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\DocumentChecklistController@show');
                    $router->post('list', 'V1\DocumentChecklistController@list');
                });
            });
            /**
             * Routes for Agent.
             */
            $router->group(['prefix' => 'agent'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\AgentController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\AgentController@update');
                    $router->post('updateStatus', 'V1\AgentController@updateStatus');
                    $router->post('updateAgentCode', 'V1\AgentController@updateAgentCode');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\AgentController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\AgentController@show');
                    $router->post('list', 'V1\AgentController@list');
                });
                $router->post('dropdown', 'V1\AgentController@dropdown');
            });
            /**
             * Routes for FOMEMA Clinics.
             */
            $router->group(['prefix' => 'fomemaClinics'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\FomemaClinicsController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\FomemaClinicsController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\FomemaClinicsController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\FomemaClinicsController@show');
                    $router->post('list', 'V1\FomemaClinicsController@list');
                });
            });
            /**
             * Routes for Fee Registration.
             */
            $router->group(['prefix' => 'feeRegistration'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\FeeRegistrationController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\FeeRegistrationController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\FeeRegistrationController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\FeeRegistrationController@show');
                    $router->post('list', 'V1\FeeRegistrationController@list');
                });
            });
            /**
             * Routes for Vendors.
             */
            $router->group(['prefix' => 'vendor'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\VendorController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\VendorController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\VendorController@delete');
                    $router->post('deleteAttachment', 'V1\VendorController@deleteAttachment');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\VendorController@show');
                    $router->post('list', 'V1\VendorController@list');                
                    $router->post('insuranceVendorList', 'V1\VendorController@insuranceVendorList');
                    $router->post('transportationVendorList', 'V1\VendorController@transportationVendorList');
                });
            });
            /**
             * Routes for Accommodation.
             */
            $router->group(['prefix' => 'accommodation'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\AccommodationController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\AccommodationController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\AccommodationController@delete');
                    $router->post('deleteAttachment', 'V1\AccommodationController@deleteAttachment');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\AccommodationController@show');
                    $router->post('list', 'V1\AccommodationController@list');     
                });           
            });
            /**
             * Routes for Insurance.
             */
            $router->group(['prefix' => 'insurance'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\InsuranceController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\InsuranceController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\InsuranceController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\InsuranceController@show');
                    $router->post('list', 'V1\InsuranceController@list');
                });
            });
            /**
             * Routes for Transportation.
             */
            $router->group(['prefix' => 'transportation'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Add']], function () use ($router) {
                    $router->post('create', 'V1\TransportationController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Edit']], function () use ($router) {
                    $router->post('update', 'V1\TransportationController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\TransportationController@delete');
                    $router->post('deleteAttachment', 'V1\TransportationController@deleteAttachment');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:2,View']], function () use ($router) {
                    $router->post('show', 'V1\TransportationController@show');
                    $router->post('list', 'V1\TransportationController@list');
                });
                $router->post('dropdown', 'V1\TransportationController@dropdown');
            });
        });
        /**
        * Routes for Branch.
        */
        $router->group(['middleware' => 'accessControl:3'], function () use ($router) {  
            $router->group(['prefix' => 'branch'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:3,Add']], function () use ($router) {
                    $router->post('create', 'V1\BranchController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:3,Edit']], function () use ($router) {
                    $router->post('update', 'V1\BranchController@update');
                    $router->post('updateStatus', 'V1\BranchController@updateStatus');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:3,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\BranchController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:3,View']], function () use ($router) {
                    $router->post('show', 'V1\BranchController@show');
                    $router->post('list', 'V1\BranchController@list');
                });                
                $router->post('dropDown', 'V1\BranchController@dropdown');
            });
        });
        /**
         * Routes for CRM.
         */
        $router->group(['middleware' => 'accessControl:4'], function () use ($router) {  
            $router->group(['prefix' => 'crm'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:4,View']], function () use ($router) {
                    $router->post('list', 'V1\CRMController@list');
                    $router->post('show', 'V1\CRMController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:4,Add']], function () use ($router) {
                    $router->post('crmImport', 'V1\CRMController@crmImport');
                    $router->post('create', 'V1\CRMController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:4,Edit']], function () use ($router) {
                    $router->post('update', 'V1\CRMController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:4,Delete']], function () use ($router) {
                    $router->post('deleteAttachment', 'V1\CRMController@deleteAttachment');               
                }); 
                $router->post('dropDownCompanies', 'V1\CRMController@dropDownCompanies');       
                $router->post('getProspectDetails', 'V1\CRMController@getProspectDetails');
                $router->post('systemList', 'V1\CRMController@systemList');        
            });
        });
        /**
         * Routes for Direct Recruitment.
         */
        $router->group(['middleware' => 'accessControl:5'], function () use ($router) {  
            $router->group(['prefix' => 'directRecruitment'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                    $router->post('addService', 'V1\DirectRecruitmentController@addService');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('applicationListing', 'V1\DirectRecruitmentController@applicationListing');
                    $router->post('totalManagementListing', 'V1\DirectRecruitmentController@totalManagementListing');
                });
                $router->post('dropDownFilter', 'V1\DirectRecruitmentController@dropDownFilter');
                /**
                * Routes for Onboarding
                */
                $router->group(['prefix' => 'onboarding'], function () use ($router) {
                    $router->group(['prefix' => 'countries'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                            $router->post('list', 'V1\DirectRecruitmentOnboardingCountryController@list');
                            $router->post('show', 'V1\DirectRecruitmentOnboardingCountryController@show');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                            $router->post('create', 'V1\DirectRecruitmentOnboardingCountryController@create');
                            $router->post('addKSM', 'V1\DirectRecruitmentOnboardingCountryController@addKSM');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                            $router->post('update', 'V1\DirectRecruitmentOnboardingCountryController@update');
                            $router->post('ksmQuotaUpdate', 'V1\DirectRecruitmentOnboardingCountryController@ksmQuotaUpdate');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Delete']], function () use ($router) {
                            $router->post('deleteKSM', 'V1\DirectRecruitmentOnboardingCountryController@deleteKSM');
                        });   
                        $router->post('ksmReferenceNumberList', 'V1\DirectRecruitmentOnboardingCountryController@ksmReferenceNumberList');
                        $router->post('ksmDropDownForOnboarding', 'V1\DirectRecruitmentOnboardingCountryController@ksmDropDownForOnboarding'); 
                    });
                    $router->group(['prefix' => 'agent'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                            $router->post('list', 'V1\DirectRecruitmentOnboardingAgentController@list');
                            $router->post('show', 'V1\DirectRecruitmentOnboardingAgentController@show');                            
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                            $router->post('create', 'V1\DirectRecruitmentOnboardingAgentController@create');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                            $router->post('update', 'V1\DirectRecruitmentOnboardingAgentController@update');
                        });     
                        $router->post('ksmDropDownBasedOnOnboarding', 'V1\DirectRecruitmentOnboardingAgentController@ksmDropDownBasedOnOnboarding');                   
                    });
                    $router->group(['prefix' => 'attestation'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                            //Attestation
                            $router->post('list', 'V1\DirectRecruitmentOnboardingAttestationController@list');
                            $router->post('show', 'V1\DirectRecruitmentOnboardingAttestationController@show');
                            //Dispatch
                            $router->post('showDispatch', 'V1\DirectRecruitmentOnboardingAttestationController@showDispatch');
                            //Embassy Attestation Costing
                            $router->post('listEmbassy', 'V1\DirectRecruitmentOnboardingAttestationController@listEmbassy');
                            $router->post('showEmbassyFile', 'V1\DirectRecruitmentOnboardingAttestationController@showEmbassyFile');
                        });

                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                            $router->post('uploadEmbassyFile', 'V1\DirectRecruitmentOnboardingAttestationController@uploadEmbassyFile');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                            $router->post('update', 'V1\DirectRecruitmentOnboardingAttestationController@update');                        
                            $router->post('updateDispatch', 'V1\DirectRecruitmentOnboardingAttestationController@updateDispatch');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Delete']], function () use ($router) {
                            $router->post('deleteEmbassyFile', 'V1\DirectRecruitmentOnboardingAttestationController@deleteEmbassyFile');
                        });
                    });
                    $router->group(['prefix' => 'workers'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                            $router->post('list', 'V1\DirectRecruitmentWorkersController@list');
                            $router->post('export', 'V1\DirectRecruitmentWorkersController@export');
                            $router->post('show', 'V1\DirectRecruitmentWorkersController@show');
                            $router->post('importHistory', 'V1\DirectRecruitmentWorkersController@importHistory');
                            $router->post('failureExport', 'V1\DirectRecruitmentWorkersController@failureExport');
                            $router->post('workerStatusList', 'V1\DirectRecruitmentWorkersController@workerStatusList');
                            $router->post('ksmDropDownBasedOnOnboardingAgent', 'V1\DirectRecruitmentWorkersController@ksmDropDownBasedOnOnboardingAgent');
                            $router->post('kinRelationship', 'V1\DirectRecruitmentWorkersController@kinRelationship');
                            $router->post('onboardingAgent', 'V1\DirectRecruitmentWorkersController@onboardingAgent');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                            $router->post('create', 'V1\DirectRecruitmentWorkersController@create');
                            $router->post('import', 'V1\DirectRecruitmentWorkersController@import');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                            $router->post('update', 'V1\DirectRecruitmentWorkersController@update');
                            $router->post('replaceWorker', 'V1\DirectRecruitmentWorkersController@replaceWorker');
                            $router->post('updateStatus', 'V1\DirectRecruitmentWorkersController@updateStatus');
                        });  
                        $router->post('dropdown', 'V1\DirectRecruitmentWorkersController@dropdown');     
                        $router->post('ksmDropDownBasedOnOnboardingAgent', 'V1\DirectRecruitmentWorkersController@ksmDropDownBasedOnOnboardingAgent'); 
                        $router->post('kinRelationship', 'V1\DirectRecruitmentWorkersController@kinRelationship');
                        $router->post('onboardingAgent', 'V1\DirectRecruitmentWorkersController@onboardingAgent');           
                    });
                    $router->group(['prefix' => 'callingVisa'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                            $router->post('callingVisaStatusList', 'V1\DirectRecruitmentCallingVisaController@callingVisaStatusList');
                            $router->post('workerListForCancellation', 'V1\DirectRecruitmentCallingVisaController@workerListForCancellation');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                            $router->post('cancelWorker', 'V1\DirectRecruitmentCallingVisaController@cancelWorker');
                        });
                        
                        $router->group(['prefix' => 'process'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                                $router->post('submitCallingVisa', 'V1\DirectRecruitmentCallingVisaController@submitCallingVisa');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentCallingVisaController@workersList');
                                $router->post('show', 'V1\DirectRecruitmentCallingVisaController@show');
                            });
                        });
                        $router->group(['prefix' => 'insurancePurchase'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentInsurancePurchaseController@workersList');
                                $router->post('show', 'V1\DirectRecruitmentInsurancePurchaseController@show');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                                $router->post('submit', 'V1\DirectRecruitmentInsurancePurchaseController@submit');
                            });
                            $router->post('insuranceProviderDropDown', 'V1\DirectRecruitmentInsurancePurchaseController@insuranceProviderDropDown');                            
                        });
                        $router->group(['prefix' => 'approval'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('approvalStatusUpdate', 'V1\DirectRecruitmentCallingVisaApprovalController@approvalStatusUpdate');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentCallingVisaApprovalController@workersList');
                                $router->post('show', 'V1\DirectRecruitmentCallingVisaApprovalController@show');
                            });
                        });
                        $router->group(['prefix' => 'immigrationFeePaid'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('update', 'V1\DirectRecruitmentImmigrationFeePaidController@update');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('listBasedOnCallingVisa', 'V1\DirectRecruitmentImmigrationFeePaidController@listBasedOnCallingVisa');
                                $router->post('workersList', 'V1\DirectRecruitmentImmigrationFeePaidController@workersList');
                                $router->post('show', 'V1\DirectRecruitmentImmigrationFeePaidController@show');
                            });
                        });
                        $router->group(['prefix' => 'generation'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('generatedStatusUpdate', 'V1\DirectRecruitmentCallingVisaGenerateController@generatedStatusUpdate');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentCallingVisaGenerateController@workersList');
                                $router->post('listBasedOnCallingVisa', 'V1\DirectRecruitmentCallingVisaGenerateController@listBasedOnCallingVisa');
                                $router->post('show', 'V1\DirectRecruitmentCallingVisaGenerateController@show');
                            });
                        });
                        $router->group(['prefix' => 'dispatch'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('update', 'V1\DirectRecruitmentCallingVisaDispatchController@update');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('listBasedOnCallingVisa', 'V1\DirectRecruitmentCallingVisaDispatchController@listBasedOnCallingVisa');
                                $router->post('workersList', 'V1\DirectRecruitmentCallingVisaDispatchController@workersList');
                                $router->post('show', 'V1\DirectRecruitmentCallingVisaDispatchController@show');
                            });
                        });
                    });
                    $router->group(['prefix' => 'arrival'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                            $router->post('list', 'V1\DirectRecruitmentArrivalController@list');
                            $router->post('show', 'V1\DirectRecruitmentArrivalController@show');
                            $router->post('workersListForUpdate', 'V1\DirectRecruitmentArrivalController@workersListForUpdate');
                            $router->post('cancelWorkerDetail', 'V1\DirectRecruitmentArrivalController@cancelWorkerDetail');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                            $router->post('submit', 'V1\DirectRecruitmentArrivalController@submit');
                            $router->post('workersListForSubmit', 'V1\DirectRecruitmentArrivalController@workersListForSubmit');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                            $router->post('update', 'V1\DirectRecruitmentArrivalController@update');
                            $router->post('cancelWorker', 'V1\DirectRecruitmentArrivalController@cancelWorker');
                            $router->post('updateWorkers', 'V1\DirectRecruitmentArrivalController@updateWorkers');
                        });
                        $router->post('callingvisaReferenceNumberList', 'V1\DirectRecruitmentArrivalController@callingvisaReferenceNumberList');
                        $router->post('arrivalDateDropDown', 'V1\DirectRecruitmentArrivalController@arrivalDateDropDown'); 
                    });
                    $router->group(['prefix' => 'postArrival'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                            $router->post('postArrivalStatusList', 'V1\DirecRecruitmentPostArrivalController@postArrivalStatusList');
                        });
                        $router->group(['prefix' => 'arrival'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirecRecruitmentPostArrivalController@workersList');
                                $router->post('workersListExport', 'V1\DirecRecruitmentPostArrivalController@workersListExport');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('updatePostArrival', 'V1\DirecRecruitmentPostArrivalController@updatePostArrival');
                                $router->post('updateJTKSubmission', 'V1\DirecRecruitmentPostArrivalController@updateJTKSubmission');
                                $router->post('updateCancellation', 'V1\DirecRecruitmentPostArrivalController@updateCancellation');
                                $router->post('updatePostponed', 'V1\DirecRecruitmentPostArrivalController@updatePostponed');
                            });                            
                        });
                        $router->group(['prefix' => 'fomema'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentPostArrivalFomemaController@workersList');
                                $router->post('workersListExport', 'V1\DirectRecruitmentPostArrivalFomemaController@workersListExport');
                                $router->post('plksShow', 'V1\DirectRecruitmentPostArrivalFomemaController@plksShow');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                                $router->post('purchase', 'V1\DirectRecruitmentPostArrivalFomemaController@purchase');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('fomemaFit', 'V1\DirectRecruitmentPostArrivalFomemaController@fomemaFit');
                                $router->post('fomemaUnfit', 'V1\DirectRecruitmentPostArrivalFomemaController@fomemaUnfit');
                                $router->post('updateSpecialPass', 'V1\DirectRecruitmentPostArrivalFomemaController@updateSpecialPass');
                            });
                            
                        });
                        $router->group(['prefix' => 'plks'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentPostArrivalPLKSController@workersList');
                                $router->post('workersListExport', 'V1\DirectRecruitmentPostArrivalPLKSController@workersListExport');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('updatePLKS', 'V1\DirectRecruitmentPostArrivalPLKSController@updatePLKS');
                                $router->post('updateSpecialPass', 'V1\DirectRecruitmentPostArrivalFomemaController@updateSpecialPass');                            
                            });
                        });
                        $router->group(['prefix' => 'repatriation'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentRepatriationController@workersList');
                                $router->post('workersListExport', 'V1\DirectRecruitmentRepatriationController@workersListExport');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('updateRepatriation', 'V1\DirectRecruitmentRepatriationController@updateRepatriation');
                            });                            
                        });
                        $router->group(['prefix' => 'specialPass'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentSpecialPassController@workersList');
                                $router->post('workersListExport', 'V1\DirectRecruitmentSpecialPassController@workersListExport');
                            });
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                                $router->post('updateSubmission', 'V1\DirectRecruitmentSpecialPassController@updateSubmission');
                                $router->post('updateValidity', 'V1\DirectRecruitmentSpecialPassController@updateValidity');        
                            });                    
                        });
                        $router->group(['prefix' => 'postponed'], function () use ($router) {
                            $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                                $router->post('workersList', 'V1\DirectRecruitmentPostponedController@workersList');
                            });
                        });
                    });
                });
            });
            /**
            * Routes for Direct recruitment.
            */
            $router->group(['prefix' => 'directRecrutment'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                    $router->post('submitProposal', 'V1\DirectRecruitmentController@submitProposal');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('showProposal', 'V1\DirectRecruitmentController@showProposal');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Delete']], function () use ($router) {
                    $router->post('deleteAttachment', 'V1\DirectRecruitmentController@deleteAttachment');
                });
            });
            /**
            * Routes for DirectRecruitmentApplicationDocumentChecklist.
            */
            $router->group(['prefix' => 'directRecruitmentApplicationChecklist'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                    $router->post('update', 'V1\DirectRecruitmentApplicationChecklistController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('show', 'V1\DirectRecruitmentApplicationChecklistController@show');
                    $router->post('showBasedOnApplication', 'V1\DirectRecruitmentApplicationChecklistController@showBasedOnApplication');
                });
            });
            /**
            * Routes for ApplicationChecklistAttachments.
            */
            $router->group(['prefix' => 'checklistAttachment'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                    $router->post('create', 'V1\ApplicationChecklistAttachmentsController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\ApplicationChecklistAttachmentsController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('list', 'V1\ApplicationChecklistAttachmentsController@list');
                });
            });
            /**
             * Routes for FWCMS.
             */
            $router->group(['prefix' => 'fwcms'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('list', 'V1\FWCMSController@list');
                    $router->post('show', 'V1\FWCMSController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                    $router->post('create', 'V1\FWCMSController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                    $router->post('update', 'V1\FWCMSController@update');
                });
            });
            /**
             * Routes for Application Interview.
             */
            $router->group(['prefix' => 'applicationInterview'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('list', 'V1\ApplicationInterviewController@list');
                    $router->post('show', 'V1\ApplicationInterviewController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                    $router->post('create', 'V1\ApplicationInterviewController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                    $router->post('update', 'V1\ApplicationInterviewController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Delete']], function () use ($router) {
                    $router->post('deleteAttachment', 'V1\ApplicationInterviewController@deleteAttachment');
                });
                $router->post('dropdownKsmReferenceNumber', 'V1\ApplicationInterviewController@dropdownKsmReferenceNumber');
            });
            /**
             * Routes for Levy.
             */
            $router->group(['prefix' => 'levy'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('list', 'V1\LevyController@list');
                    $router->post('show', 'V1\LevyController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                    $router->post('create', 'V1\LevyController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                    $router->post('update', 'V1\LevyController@update');
                });
            });
            /**
            * Routes for DirectRecruitmentApplicationApproval.
            */
            $router->group(['prefix' => 'directRecruitmentApplicationApproval'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('list', 'V1\DirectRecruitmentApplicationApprovalController@list');
                    $router->post('show', 'V1\DirectRecruitmentApplicationApprovalController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                    $router->post('create', 'V1\DirectRecruitmentApplicationApprovalController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                    $router->post('update', 'V1\DirectRecruitmentApplicationApprovalController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Delete']], function () use ($router) {
                    $router->post('deleteAttachment', 'V1\DirectRecruitmentApplicationApprovalController@deleteAttachment');
                });
            });
            /**
            * Routes for Application Summary.
            */
            $router->group(['prefix' => 'applicationSummary'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('list', 'V1\ApplicationSummaryController@list');
                    $router->post('listKsmReferenceNumber', 'V1\ApplicationSummaryController@listKsmReferenceNumber');
                });
            });
            /**
            * Routes for Direct Recruitment Expenses.
            */
            $router->group(['prefix' => 'directRecrutmentExpenses'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,View']], function () use ($router) {
                    $router->post('list', 'V1\DirectRecruitmentExpensesController@list');
                    $router->post('show', 'V1\DirectRecruitmentExpensesController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Add']], function () use ($router) {
                    $router->post('create', 'V1\DirectRecruitmentExpensesController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Edit']], function () use ($router) {
                    $router->post('update', 'V1\DirectRecruitmentExpensesController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:5,Delete']], function () use ($router) {
                    $router->post('deleteAttachment', 'V1\DirectRecruitmentExpensesController@deleteAttachment');
                });
            });
        });

        $router->group(['prefix' => 'manageWorkers'], function () use ($router) {
            $router->group(['prefix' => 'worker'], function () use ($router) {
                $router->post('exportTemplate', 'V1\ManageWorkersController@exportTemplate');
            });
        });

        /**
        * Routes for Total Management.
        */
        $router->group(['middleware' => 'accessControl:6'], function () use ($router) {  
            $router->group(['prefix' => 'eContract'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:6,Add']], function () use ($router) {
                    $router->post('addService', 'V1\EContractController@addService');
                    $router->post('proposalSubmit', 'V1\EContractController@proposalSubmit');
                    $router->post('allocateQuota', 'V1\EContractController@allocateQuota');
                });

                $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                    $router->post('applicationListing', 'V1\EContractController@applicationListing');
                    $router->post('showProposal', 'V1\EContractController@showProposal');
                    $router->post('showService', 'V1\EContractController@showService'); 
                });               
                
                $router->group(['prefix' => 'project'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                        $router->post('list', 'V1\EContractProjectController@list');
                        $router->post('show', 'V1\EContractProjectController@show');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,Add']], function () use ($router) {
                        $router->post('add', 'V1\EContractProjectController@add');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,Edit']], function () use ($router) {
                        $router->post('update', 'V1\EContractProjectController@update');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,Delete']], function () use ($router) {
                        $router->post('deleteAttachment', 'V1\EContractProjectController@deleteAttachment');
                    });
                });
                $router->group(['prefix' => 'manage'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                        $router->post('list', 'V1\EContractWorkerController@list');
                    });
                    $router->group(['prefix' => 'workerAssign'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                            $router->post('workerListForAssignWorker', 'V1\EContractWorkerController@workerListForAssignWorker');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Edit']], function () use ($router) {
                            $router->post('assignWorker', 'V1\EContractWorkerController@assignWorker');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Delete']], function () use ($router) {
                            $router->post('removeWorker', 'V1\EContractWorkerController@removeWorker');
                        });
                    });
                    $router->group(['prefix' => 'workerEvent'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                            $router->post('list', 'V1\EContractWorkerEventController@list');
                            $router->post('show', 'V1\EContractWorkerEventController@show');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Add']], function () use ($router) {
                            $router->post('create', 'V1\EContractWorkerEventController@create');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Edit']], function () use ($router) {
                            $router->post('update', 'V1\EContractWorkerEventController@update');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Delete']], function () use ($router) {
                            $router->post('deleteAttachment', 'V1\EContractWorkerEventController@deleteAttachment');
                        });
                    });
                    $router->group(['prefix' => 'transfer'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                            $router->post('workerEmploymentDetail', 'V1\EContractTransferController@workerEmploymentDetail');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Add']], function () use ($router) {
                            $router->post('submit', 'V1\EContractTransferController@submit');
                        });
                        $router->post('companyList', 'V1\EContractTransferController@companyList');
                        $router->post('projectList', 'V1\EContractTransferController@projectList');
                    });
                    $router->group(['prefix' => 'expense'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                            $router->post('list', 'V1\EContractExpensesController@list');
                            $router->post('show', 'V1\EContractExpensesController@show');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Add']], function () use ($router) {
                            $router->post('create', 'V1\EContractExpensesController@create');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Edit']], function () use ($router) {
                            $router->post('update', 'V1\EContractExpensesController@update');
                            $router->post('payBack', 'V1\EContractExpensesController@payBack');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:6,Delete']], function () use ($router) {
                            $router->post('delete', 'V1\EContractExpensesController@delete');
                            $router->post('deleteAttachment', 'V1\EContractExpensesController@deleteAttachment');                        
                        });
                    });
                });
                
                $router->group(['prefix' => 'costManagement'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                        $router->post('list', 'V1\EContractCostManagementController@list');
                        $router->post('show', 'V1\EContractCostManagementController@show');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,Add']], function () use ($router) {
                        $router->post('create', 'V1\EContractCostManagementController@create');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,Edit']], function () use ($router) {
                        $router->post('update', 'V1\EContractCostManagementController@update');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,Delete']], function () use ($router) {
                        $router->post('delete', 'V1\EContractCostManagementController@delete');
                        $router->post('deleteAttachment', 'V1\EContractCostManagementController@deleteAttachment');
                    });
                });
                $router->group(['prefix' => 'payroll'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,View']], function () use ($router) {
                        $router->post('projectDetails', 'V1\EContractPayrollController@projectDetails');
                        $router->post('list', 'V1\EContractPayrollController@list');
                        $router->post('show', 'V1\EContractPayrollController@show');
                        $router->post('export', 'V1\EContractPayrollController@export');
                        $router->post('listTimesheet', 'V1\EContractPayrollController@listTimesheet');
                        $router->post('viewTimesheet', 'V1\EContractPayrollController@viewTimesheet');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,Add']], function () use ($router) {
                        $router->post('import', 'V1\EContractPayrollController@import');
                        $router->post('add', 'V1\EContractPayrollController@add');
                        $router->post('uploadTimesheet', 'V1\EContractPayrollController@uploadTimesheet');
                        $router->post('authorizePayroll', 'V1\EContractPayrollController@authorizePayroll');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:6,Edit']], function () use ($router) {
                        $router->post('update', 'V1\EContractPayrollController@update');
                    });
                    
                });
            });
        });
        /**
        * Routes for Total Management.
        */
        $router->group(['middleware' => 'accessControl:7'], function () use ($router) {  
            $router->group(['prefix' => 'totalManagement'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                    $router->post('applicationListing', 'V1\TotalManagementController@applicationListing');
                    $router->post('showProposal', 'V1\TotalManagementController@showProposal');
                    $router->post('showService', 'V1\TotalManagementController@showService');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:7,Add']], function () use ($router) {
                    $router->post('addService', 'V1\TotalManagementController@addService');
                    $router->post('getQuota', 'V1\TotalManagementController@getQuota');                
                    $router->post('submitProposal', 'V1\TotalManagementController@submitProposal');
                    $router->post('allocateQuota', 'V1\TotalManagementController@allocateQuota');
                });
                
                $router->group(['prefix' => 'project'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                        $router->post('list', 'V1\TotalManagementProjectController@list');
                        $router->post('show', 'V1\TotalManagementProjectController@show');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,Add']], function () use ($router) {
                        $router->post('add', 'V1\TotalManagementProjectController@add');
                        $router->post('update', 'V1\TotalManagementProjectController@update');
                    });
                });
                $router->group(['prefix' => 'supervisor'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                        $router->post('list', 'V1\TotalManagementSupervisorController@list');
                        $router->post('viewAssignments', 'V1\TotalManagementSupervisorController@viewAssignments');
                    });
                });
                $router->group(['prefix' => 'manage'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                        $router->post('list', 'V1\TotalManagementWorkerController@list');
                    });
                    $router->group(['prefix' => 'workerAssign'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                            $router->post('workerListForAssignWorker', 'V1\TotalManagementWorkerController@workerListForAssignWorker');
                            $router->post('getAssignedWorker', 'V1\TotalManagementWorkerController@getAssignedWorker');                        
                            $router->post('getBalancedQuota', 'V1\TotalManagementWorkerController@getBalancedQuota');
                            $router->post('getCompany', 'V1\TotalManagementWorkerController@getCompany');
                            $router->post('getSectorAndValidUntil', 'V1\TotalManagementWorkerController@getSectorAndValidUntil');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,Add']], function () use ($router) {
                            $router->post('assignWorker', 'V1\TotalManagementWorkerController@assignWorker');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,Delete']], function () use ($router) {
                            $router->post('removeWorker', 'V1\TotalManagementWorkerController@removeWorker');
                        });
                        $router->post('accommodationProviderDropDown', 'V1\TotalManagementWorkerController@accommodationProviderDropDown');
                        $router->post('accommodationUnitDropDown', 'V1\TotalManagementWorkerController@accommodationUnitDropDown');
                        $router->post('ksmRefereneceNUmberDropDown', 'V1\TotalManagementWorkerController@ksmRefereneceNUmberDropDown');
                    });
                    $router->group(['prefix' => 'workerEvent'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                            $router->post('list', 'V1\TotalManagementWorkerEventController@list');
                            $router->post('show', 'V1\TotalManagementWorkerEventController@show');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,Add']], function () use ($router) {
                            $router->post('create', 'V1\TotalManagementWorkerEventController@create');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,Edit']], function () use ($router) {
                            $router->post('update', 'V1\TotalManagementWorkerEventController@update');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,Delete']], function () use ($router) {
                            $router->post('deleteAttachment', 'V1\TotalManagementWorkerEventController@deleteAttachment');
                        });
                    });
                    $router->group(['prefix' => 'expense'], function () use ($router) {
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                            $router->post('list', 'V1\TotalManagementExpensesController@list');
                            $router->post('show', 'V1\TotalManagementExpensesController@show');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,Add']], function () use ($router) {
                            $router->post('create', 'V1\TotalManagementExpensesController@create');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,Edit']], function () use ($router) {
                            $router->post('update', 'V1\TotalManagementExpensesController@update');
                            $router->post('payBack', 'V1\TotalManagementExpensesController@payBack');
                        });
                        $router->group(['prefix' => '', 'middleware' => ['permissions:7,Delete']], function () use ($router) {
                            $router->post('delete', 'V1\TotalManagementExpensesController@delete');
                            $router->post('deleteAttachment', 'V1\TotalManagementExpensesController@deleteAttachment');
                        });                        
                    });
                });
                $router->group(['prefix' => 'payroll'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                        $router->post('projectDetails', 'V1\TotalManagementPayrollController@projectDetails');
                        $router->post('list', 'V1\TotalManagementPayrollController@list');
                        $router->post('export', 'V1\TotalManagementPayrollController@export');
                        $router->post('show', 'V1\TotalManagementPayrollController@show');
                        $router->post('listTimesheet', 'V1\TotalManagementPayrollController@listTimesheet');
                        $router->post('viewTimesheet', 'V1\TotalManagementPayrollController@viewTimesheet');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,Add']], function () use ($router) {
                        $router->post('import', 'V1\TotalManagementPayrollController@import');
                        $router->post('add', 'V1\TotalManagementPayrollController@add');
                        $router->post('uploadTimesheet', 'V1\TotalManagementPayrollController@uploadTimesheet');
                        $router->post('authorizePayroll', 'V1\TotalManagementPayrollController@authorizePayroll');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,Edit']], function () use ($router) {
                        $router->post('update', 'V1\TotalManagementPayrollController@update');
                    });
                    
                });
                $router->group(['prefix' => 'transfer'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                        $router->post('workerEmploymentDetail', 'V1\TotalManagementTransferController@workerEmploymentDetail');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,Add']], function () use ($router) {
                        $router->post('submit', 'V1\TotalManagementTransferController@submit');
                    });
                    $router->post('companyList', 'V1\TotalManagementTransferController@companyList');
                    $router->post('projectList', 'V1\TotalManagementTransferController@projectList');
                });
    
                /**
                * Routes for Total Management Cost Management.
                */
                $router->group(['prefix' => 'costManagement'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,View']], function () use ($router) {
                        $router->post('list', 'V1\TotalManagementCostManagementController@list');
                        $router->post('show', 'V1\TotalManagementCostManagementController@show');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,Add']], function () use ($router) {
                        $router->post('create', 'V1\TotalManagementCostManagementController@create');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,Edit']], function () use ($router) {
                        $router->post('update', 'V1\TotalManagementCostManagementController@update');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:7,Delete']], function () use ($router) {
                        $router->post('delete', 'V1\TotalManagementCostManagementController@delete');
                        $router->post('deleteAttachment', 'V1\TotalManagementCostManagementController@deleteAttachment');
                    });
                });
            });
        });
        /**
         * Routes for Employees.
         */
        $router->group(['middleware' => 'accessControl:8'], function () use ($router) {  
            $router->group(['prefix' => 'employee'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:8,Add']], function () use ($router) {
                    $router->post('create', 'V1\EmployeeController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:8,Edit']], function () use ($router) {
                    $router->post('update', 'V1\EmployeeController@update');
                    $router->post('updateStatus', 'V1\EmployeeController@updateStatus');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:8,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\EmployeeController@delete');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:8,View']], function () use ($router) {
                    $router->post('list', 'V1\EmployeeController@list');
                    $router->post('show', 'V1\EmployeeController@show');
                });
                $router->post('dropDown', 'V1\EmployeeController@dropdown');
                $router->post('supervisorList', 'V1\EmployeeController@supervisorList');
            });
        });
        /**
         * Routes for Roles.
         */
        $router->group(['middleware' => 'accessControl:9'], function () use ($router) {  
            $router->group(['prefix' => 'role'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:9,View']], function () use ($router) {
                    $router->post('list', 'V1\RolesController@list');
                    $router->post('show', 'V1\RolesController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:9,Add']], function () use ($router) {
                    $router->post('create', 'V1\RolesController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:9,Edit']], function () use ($router) {
                    $router->post('update', 'V1\RolesController@update');
                    $router->post('updateStatus', 'V1\RolesController@updateStatus');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:9,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\RolesController@delete');
                });
                $router->post('dropDown', 'V1\RolesController@dropDown');
            });
            /**
             * Routes for Access Management.
             */
            $router->group(['prefix' => 'accessManagement'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:9,Add']], function () use ($router) {
                    $router->post('create', 'V1\AccessManagementController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:9,Edit']], function () use ($router) {
                    $router->post('update', 'V1\AccessManagementController@update');
                });
            });
        });
        /**
         * Routes for Modules List - Using this API To Display the Left Menu for All the Users types in Front-End.
         */
        $router->group(['prefix' => 'accessManagement'], function () use ($router) {
            $router->post('list', 'V1\AccessManagementController@list');
        });

        /**
        * Routes for Application Summary.
        */
        $router->group(['middleware' => 'accessControl:10'], function () use ($router) {  
            $router->group(['prefix' => 'worker'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:10,View']], function () use ($router) {
                    $router->post('list', 'V1\WorkersController@list');
                    $router->post('show', 'V1\WorkersController@show');
                    $router->post('export', 'V1\WorkersController@export');
                    $router->post('workerStatusList', 'V1\WorkersController@workerStatusList');
                    $router->post('listAttachment', 'V1\WorkersController@listAttachment');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:10,Add']], function () use ($router) {
                    $router->post('create', 'V1\WorkersController@create');
                    $router->post('addAttachment', 'V1\WorkersController@addAttachment');
                    $router->post('assignWorker', 'V1\WorkersController@assignWorker');
                    $router->post('import', 'V1\WorkersController@import');
                    $router->post('importHistory', 'V1\WorkersController@importHistory');
                    $router->post('failureExport', 'V1\WorkersController@failureExport');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:10,Edit']], function () use ($router) {
                    $router->post('update', 'V1\WorkersController@update');
                    $router->post('updateStatus', 'V1\WorkersController@updateStatus');
                    $router->post('replaceWorker', 'V1\WorkersController@replaceWorker');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:10,Delete']], function () use ($router) {
                    $router->post('deleteAttachment', 'V1\WorkersController@deleteAttachment');
                });
                $router->post('dropdown', 'V1\WorkersController@dropdown');
                $router->post('kinRelationship', 'V1\WorkersController@kinRelationship');
                $router->post('onboardingAgent', 'V1\WorkersController@onboardingAgent');

                $router->group(['prefix' => 'workerEvent'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:10,View']], function () use ($router) {
                        $router->post('list', 'V1\WorkerEventController@list');
                        $router->post('show', 'V1\WorkerEventController@show');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:10,Add']], function () use ($router) {
                        $router->post('create', 'V1\WorkerEventController@create');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:10,Edit']], function () use ($router) {
                        $router->post('update', 'V1\WorkerEventController@update');                    
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:10,Delete']], function () use ($router) {
                        $router->post('deleteAttachment', 'V1\WorkerEventController@deleteAttachment');
                    });
                });
                $router->group(['prefix' => 'bankdetails'], function () use ($router) {
                    $router->group(['prefix' => '', 'middleware' => ['permissions:10,View']], function () use ($router) {
                        $router->post('list', 'V1\WorkersController@listBankDetails');
                        $router->post('show', 'V1\WorkersController@showBankDetails');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:10,Add']], function () use ($router) {
                        $router->post('create', 'V1\WorkersController@createBankDetails');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:10,Edit']], function () use ($router) {
                        $router->post('update', 'V1\WorkersController@updateBankDetails');
                    });
                    $router->group(['prefix' => '', 'middleware' => ['permissions:10,Delete']], function () use ($router) {
                        $router->post('delete', 'V1\WorkersController@deleteBankDetails');
                    });
                });
            });
        });
        /**
         * Routes for Dispatch Management
         */
        $router->group(['middleware' => 'accessControl:11'], function () use ($router) {  
            $router->group(['prefix' => 'dispatchManagement'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:11,View']], function () use ($router) {
                    $router->post('list', 'V1\DispatchManagementController@list');
                    $router->post('show', 'V1\DispatchManagementController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:11,Add']], function () use ($router) {
                    $router->post('create', 'V1\DispatchManagementController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:11,Edit']], function () use ($router) {
                    $router->post('update', 'V1\DispatchManagementController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:11,Delete']], function () use ($router) {
                    $router->post('deleteAttachment', 'V1\DispatchManagementController@deleteAttachment');
                });
            });
        });
        /**
        * Routes for Invoice.
        */
        $router->group(['middleware' => 'accessControl:12'], function () use ($router) {  
            $router->group(['prefix' => 'invoice'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:12,View']], function () use ($router) {
                    $router->post('list', 'V1\InvoiceController@list');
                    $router->post('show', 'V1\InvoiceController@show');  
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:12,Add']], function () use ($router) {
                    $router->post('create', 'V1\InvoiceController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:12,Edit']], function () use ($router) {
                    $router->post('update', 'V1\InvoiceController@update');
                });
                $router->post('getTaxRates', 'V1\InvoiceController@getTaxRates');
                $router->post('getItems', 'V1\InvoiceController@getItems');
                $router->post('getAccounts', 'V1\InvoiceController@getAccounts');
                $router->post('getInvoices', 'V1\InvoiceController@getInvoices');
                $router->post('getAccessToken', 'V1\InvoiceController@getAccessToken');
                $router->post('xeroGetTaxRates', 'V1\InvoiceController@xeroGetTaxRates');
                $router->post('xeroGetAccounts', 'V1\InvoiceController@xeroGetAccounts');
                $router->post('xeroGetItems', 'V1\InvoiceController@xeroGetItems');
            });

            /**
            * Routes for Invoice Temp.
            */
            $router->group(['prefix' => 'invoiceItemsTemp'], function () use ($router) {
                $router->group(['prefix' => '', 'middleware' => ['permissions:12,View']], function () use ($router) {
                    $router->post('list', 'V1\InvoiceItemsTempController@list');
                    $router->post('show', 'V1\InvoiceItemsTempController@show');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:12,Add']], function () use ($router) {
                    $router->post('create', 'V1\InvoiceItemsTempController@create');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:12,Edit']], function () use ($router) {
                    $router->post('update', 'V1\InvoiceItemsTempController@update');
                });
                $router->group(['prefix' => '', 'middleware' => ['permissions:12,Delete']], function () use ($router) {
                    $router->post('delete', 'V1\InvoiceItemsTempController@delete');
                    $router->post('deleteAll', 'V1\InvoiceItemsTempController@deleteAll');
                });
            });
        });
        /**
         * Routes for Reports.
         */
        $router->group(['middleware' => 'accessControl:13'], function () use ($router) {  
            $router->group(['prefix' => '', 'middleware' => ['permissions:13,View']], function () use ($router) {
                $router->group(['prefix' => 'reports'], function () use ($router) {
                    $router->group(['prefix' => 'serviceAgreement'], function () use ($router) {
                        $router->post('list', 'V1\ServiceAgreementReportController@list');
                    });
                    $router->group(['prefix' => 'availableWorkers'], function () use ($router) {
                        $router->post('list', 'V1\AvailableWorkersReportController@list');
                    });
                    $router->group(['prefix' => 'workerStatistics'], function () use ($router) {
                        $router->post('list', 'V1\WorkerStatisticsReportController@list');
                    });
                });
            });
        });
    });
});