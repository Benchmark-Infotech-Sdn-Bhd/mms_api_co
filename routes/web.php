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
            $router->get('refresh', 'V1\AuthController@refresh');
            $router->post('adminList', 'V1\UserController@adminList');
            $router->post('adminShow', 'V1\UserController@adminShow');
            $router->post('adminUpdate', 'V1\UserController@adminUpdate');
            $router->post('adminUpdateStatus', 'V1\UserController@adminUpdateStatus');
        });
        /**
         * Routes for Company.
         */
        $router->group(['prefix' => 'company'], function () use ($router) {
            $router->post('list', 'V1\CompanyController@list');
            $router->post('show', 'V1\CompanyController@show');
            $router->post('create', 'V1\CompanyController@create');
            $router->post('update', 'V1\CompanyController@update');
            $router->post('updateStatus', 'V1\CompanyController@updateStatus');
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
            $router->post('updateStatus', 'V1\RolesController@updateStatus');
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
        /**
         * Routes for Countries.
         */
        $router->group(['prefix' => 'country'], function () use ($router) {
            $router->post('create', 'V1\CountriesController@create');
            $router->post('update', 'V1\CountriesController@update');
            $router->post('delete', 'V1\CountriesController@delete');
            $router->post('show', 'V1\CountriesController@show');
            $router->post('dropDown', 'V1\CountriesController@dropdown');
            $router->post('list', 'V1\CountriesController@list');
            $router->post('updateStatus', 'V1\CountriesController@updateStatus');
        });
        /**
         * Routes for EmbassyAttestationFileCosting.
         */
        $router->group(['prefix' => 'embassyAttestationFile'], function () use ($router) {
            $router->post('create', 'V1\EmbassyAttestationFileCostingController@create');
            $router->post('update', 'V1\EmbassyAttestationFileCostingController@update');
            $router->post('delete', 'V1\EmbassyAttestationFileCostingController@delete');
            $router->post('show', 'V1\EmbassyAttestationFileCostingController@show');
            $router->post('list', 'V1\EmbassyAttestationFileCostingController@list');
        });
        /**
         * Routes for Sectors.
         */
        $router->group(['prefix' => 'sector'], function () use ($router) {
            $router->post('create', 'V1\SectorsController@create');
            $router->post('update', 'V1\SectorsController@update');
            $router->post('delete', 'V1\SectorsController@delete');
            $router->post('show', 'V1\SectorsController@show');
            $router->post('dropDown', 'V1\SectorsController@dropdown');
            $router->post('list', 'V1\SectorsController@list');
            $router->post('updateStatus', 'V1\SectorsController@updateStatus');
        });
        /**
         * Routes for DocumentChecklist.
         */
        $router->group(['prefix' => 'documentChecklist'], function () use ($router) {
            $router->post('create', 'V1\DocumentChecklistController@create');
            $router->post('update', 'V1\DocumentChecklistController@update');
            $router->post('delete', 'V1\DocumentChecklistController@delete');
            $router->post('show', 'V1\DocumentChecklistController@show');
            $router->post('list', 'V1\DocumentChecklistController@list');
        });
        /**
         * Routes for Agent.
         */
        $router->group(['prefix' => 'agent'], function () use ($router) {
            $router->post('create', 'V1\AgentController@create');
            $router->post('update', 'V1\AgentController@update');
            $router->post('delete', 'V1\AgentController@delete');
            $router->post('show', 'V1\AgentController@show');
            $router->post('list', 'V1\AgentController@list');
            $router->post('updateStatus', 'V1\AgentController@updateStatus');
            $router->post('dropdown', 'V1\AgentController@dropdown');
        });
        /**
         * Routes for Employees.
         */
        $router->group(['prefix' => 'employee'], function () use ($router) {
            $router->post('create', 'V1\EmployeeController@create');
            $router->post('update', 'V1\EmployeeController@update');
            $router->post('delete', 'V1\EmployeeController@delete');
            $router->post('show', 'V1\EmployeeController@show');
            $router->post('updateStatus', 'V1\EmployeeController@updateStatus');
            $router->post('list', 'V1\EmployeeController@list');
            $router->post('dropDown', 'V1\EmployeeController@dropdown');
        });
        /**
         * Routes for CRM.
         */
        $router->group(['prefix' => 'crm'], function () use ($router) {
            $router->post('list', 'V1\CRMController@list');
            $router->post('show', 'V1\CRMController@show');
            $router->post('create', 'V1\CRMController@create');
            $router->post('update', 'V1\CRMController@update');
            $router->post('deleteAttachment', 'V1\CRMController@deleteAttachment');
            $router->post('dropDownCompanies', 'V1\CRMController@dropDownCompanies');
            $router->post('getProspectDetails', 'V1\CRMController@getProspectDetails');
            $router->post('systemList', 'V1\CRMController@systemList');
        });
        /**
         * Routes for Vendors.
         */
        $router->group(['prefix' => 'vendor'], function () use ($router) {
            $router->post('create', 'V1\VendorController@create');
            $router->post('update', 'V1\VendorController@update');
            $router->post('delete', 'V1\VendorController@delete');
            $router->post('show', 'V1\VendorController@show');
            $router->post('list', 'V1\VendorController@list');
            $router->post('search', 'V1\VendorController@search');
            $router->post('deleteAttachment', 'V1\VendorController@deleteAttachment');
            $router->post('filter', 'V1\VendorController@filter');
            $router->post('insuranceVendorList', 'V1\VendorController@insuranceVendorList');
        });
        /**
         * Routes for FOMEMA Clinics.
         */
        $router->group(['prefix' => 'fomemaClinics'], function () use ($router) {
            $router->post('create', 'V1\FomemaClinicsController@create');
            $router->post('update', 'V1\FomemaClinicsController@update');
            $router->post('delete', 'V1\FomemaClinicsController@delete');
            $router->post('show', 'V1\FomemaClinicsController@show');
            $router->post('list', 'V1\FomemaClinicsController@list');
            $router->post('search', 'V1\FomemaClinicsController@search');
        });
        /**
         * Routes for Fee Registration.
         */
        $router->group(['prefix' => 'feeRegistration'], function () use ($router) {
            $router->post('create', 'V1\FeeRegistrationController@create');
            $router->post('update', 'V1\FeeRegistrationController@update');
            $router->post('delete', 'V1\FeeRegistrationController@delete');
            $router->post('show', 'V1\FeeRegistrationController@show');
            $router->post('list', 'V1\FeeRegistrationController@list');
            $router->post('search', 'V1\FeeRegistrationController@search');
        });
        /**
         * Routes for Accommodation.
         */
        $router->group(['prefix' => 'accommodation'], function () use ($router) {
            $router->post('create', 'V1\AccommodationController@create');
            $router->post('update', 'V1\AccommodationController@update');
            $router->post('delete', 'V1\AccommodationController@delete');
            $router->post('show', 'V1\AccommodationController@show');
            $router->post('list', 'V1\AccommodationController@list');
            $router->post('search', 'V1\AccommodationController@search');
            $router->post('deleteAttachment', 'V1\AccommodationController@deleteAttachment');
        });
        /**
         * Routes for Insurance.
         */
        $router->group(['prefix' => 'insurance'], function () use ($router) {
            $router->post('create', 'V1\InsuranceController@create');
            $router->post('update', 'V1\InsuranceController@update');
            $router->post('delete', 'V1\InsuranceController@delete');
            $router->post('show', 'V1\InsuranceController@show');
            $router->post('list', 'V1\InsuranceController@list');
            $router->post('search', 'V1\InsuranceController@search');
        });
        /**
         * Routes for Transportation.
         */
        $router->group(['prefix' => 'transportation'], function () use ($router) {
            $router->post('create', 'V1\TransportationController@create');
            $router->post('update', 'V1\TransportationController@update');
            $router->post('delete', 'V1\TransportationController@delete');
            $router->post('show', 'V1\TransportationController@show');
            $router->post('list', 'V1\TransportationController@list');
            $router->post('search', 'V1\TransportationController@search');
            $router->post('deleteAttachment', 'V1\TransportationController@deleteAttachment');
        });

        /**
        * Routes for Branch.
        */
        $router->group(['prefix' => 'branch'], function () use ($router) {
            $router->post('create', 'V1\BranchController@create');
            $router->post('update', 'V1\BranchController@update');
            $router->post('delete', 'V1\BranchController@delete');
            $router->post('show', 'V1\BranchController@show');
            $router->post('list', 'V1\BranchController@list');
            $router->post('search', 'V1\BranchController@search');
            $router->post('dropDown', 'V1\BranchController@dropdown');
            $router->post('updateStatus', 'V1\BranchController@updateStatus');
        });
        /**
         * Routes for Direct Recruitment.
         */
        $router->group(['prefix' => 'directRecruitment'], function () use ($router) {
            $router->post('addService', 'V1\DirectRecruitmentController@addService');
            $router->post('applicationListing', 'V1\DirectRecruitmentController@applicationListing');
            $router->post('dropDownFilter', 'V1\DirectRecruitmentController@dropDownFilter');

            /**
            * Routes for Onboarding
            */
            $router->group(['prefix' => 'onboarding'], function () use ($router) {
                $router->group(['prefix' => 'countries'], function () use ($router) {
                    $router->post('list', 'V1\DirectRecruitmentOnboardingCountryController@list');
                    $router->post('show', 'V1\DirectRecruitmentOnboardingCountryController@show');
                    $router->post('create', 'V1\DirectRecruitmentOnboardingCountryController@create');
                    $router->post('update', 'V1\DirectRecruitmentOnboardingCountryController@update');
                    $router->post('ksmReferenceNumberList', 'V1\DirectRecruitmentOnboardingCountryController@ksmReferenceNumberList');
                });
                $router->group(['prefix' => 'agent'], function () use ($router) {
                    $router->post('list', 'V1\DirectRecruitmentOnboardingAgentController@list');
                    $router->post('show', 'V1\DirectRecruitmentOnboardingAgentController@show');
                    $router->post('create', 'V1\DirectRecruitmentOnboardingAgentController@create');
                    $router->post('update', 'V1\DirectRecruitmentOnboardingAgentController@update');
                });
                $router->group(['prefix' => 'attestation'], function () use ($router) {
                    //Attestation
                    $router->post('list', 'V1\DirectRecruitmentOnboardingAttestationController@list');
                    $router->post('show', 'V1\DirectRecruitmentOnboardingAttestationController@show');
                    $router->post('create', 'V1\DirectRecruitmentOnboardingAttestationController@create');
                    $router->post('update', 'V1\DirectRecruitmentOnboardingAttestationController@update');
                    //Dispatch
                    $router->post('showDispatch', 'V1\DirectRecruitmentOnboardingAttestationController@showDispatch');
                    $router->post('updateDispatch', 'V1\DirectRecruitmentOnboardingAttestationController@updateDispatch');
                    //Embassy Attestation Costing
                    $router->post('listEmbassy', 'V1\DirectRecruitmentOnboardingAttestationController@listEmbassy');
                    $router->post('showEmbassyFile', 'V1\DirectRecruitmentOnboardingAttestationController@showEmbassyFile');
                    $router->post('uploadEmbassyFile', 'V1\DirectRecruitmentOnboardingAttestationController@uploadEmbassyFile');
                    $router->post('deleteEmbassyFile', 'V1\DirectRecruitmentOnboardingAttestationController@deleteEmbassyFile');
                });
                $router->group(['prefix' => 'callingVisa'], function () use ($router) {
                    $router->post('callingVisaStatusList', 'V1\DirectRecruitmentCallingVisaController@callingVisaStatusList');
                    $router->post('cancelWorker', 'V1\DirectRecruitmentCallingVisaController@cancelWorker');
                    $router->group(['prefix' => 'process'], function () use ($router) {
                        $router->post('submitCallingVisa', 'V1\DirectRecruitmentCallingVisaController@submitCallingVisa');
                        $router->post('workersList', 'V1\DirectRecruitmentCallingVisaController@workersList');
                        $router->post('show', 'V1\DirectRecruitmentCallingVisaController@show');
                    });
                    $router->group(['prefix' => 'insurancePurchase'], function () use ($router) {
                        $router->post('workersList', 'V1\DirectRecruitmentInsurancePurchaseController@workersList');
                        $router->post('show', 'V1\DirectRecruitmentInsurancePurchaseController@show');
                        $router->post('submit', 'V1\DirectRecruitmentInsurancePurchaseController@submit');
                    });
                    $router->group(['prefix' => 'approval'], function () use ($router) {
                        $router->post('approvalStatusUpdate', 'V1\DirectRecruitmentCallingVisaApprovalController@approvalStatusUpdate');
                        $router->post('workersList', 'V1\DirectRecruitmentCallingVisaApprovalController@workersList');
                        $router->post('show', 'V1\DirectRecruitmentCallingVisaApprovalController@show');
                    });
                    $router->group(['prefix' => 'immigrationFeePaid'], function () use ($router) {
                        $router->post('listBasedOnCallingVisa', 'V1\DirectRecruitmentImmigrationFeePaidController@listBasedOnCallingVisa');
                        $router->post('update', 'V1\DirectRecruitmentImmigrationFeePaidController@update');
                        $router->post('workersList', 'V1\DirectRecruitmentImmigrationFeePaidController@workersList');
                    });
                    $router->group(['prefix' => 'generation'], function () use ($router) {
                        $router->post('generatedStatusUpdate', 'V1\DirectRecruitmentCallingVisaGenerateController@generatedStatusUpdate');
                        $router->post('workersList', 'V1\DirectRecruitmentCallingVisaGenerateController@workersList');
                        $router->post('listBasedOnCallingVisa', 'V1\DirectRecruitmentCallingVisaGenerateController@listBasedOnCallingVisa');
                    });
                    $router->group(['prefix' => 'dispatch'], function () use ($router) {
                        $router->post('listBasedOnCallingVisa', 'V1\DirectRecruitmentCallingVisaDispatchController@listBasedOnCallingVisa');
                        $router->post('update', 'V1\DirectRecruitmentCallingVisaDispatchController@update');
                        $router->post('workersList', 'V1\DirectRecruitmentCallingVisaDispatchController@workersList');
					});
                });
                $router->group(['prefix' => 'postArrival'], function () use ($router) {
                    $router->post('updatePostArrival', 'V1\DirecRecruitmentPostArrivalController@updatePostArrival');
                    $router->post('UpdateJTKSubmission', 'V1\DirecRecruitmentPostArrivalController@UpdateJTKSubmission');
                    $router->post('updateCancellation', 'V1\DirecRecruitmentPostArrivalController@updateCancellation');
                    $router->post('updatePostponed', 'V1\DirecRecruitmentPostArrivalController@updatePostponed');
                });
            });
        });
        /**
        * Routes for Direct recruitment.
        */
        $router->group(['prefix' => 'directRecrutment'], function () use ($router) {
            $router->post('submitProposal', 'V1\DirectRecruitmentController@submitProposal');
            $router->post('showProposal', 'V1\DirectRecruitmentController@showProposal');
            $router->post('deleteAttachment', 'V1\DirectRecruitmentController@deleteAttachment');
        });

        /**
        * Routes for ApplicationChecklistAttachments.
        */
        $router->group(['prefix' => 'checklistAttachment'], function () use ($router) {
            $router->post('create', 'V1\ApplicationChecklistAttachmentsController@create');
            $router->post('delete', 'V1\ApplicationChecklistAttachmentsController@delete');
            $router->post('list', 'V1\ApplicationChecklistAttachmentsController@list');
        });

        /**
        * Routes for DirectRecruitmentApplicationDocumentChecklist.
        */
        $router->group(['prefix' => 'directRecruitmentApplicationChecklist'], function () use ($router) {
            $router->post('update', 'V1\DirectRecruitmentApplicationChecklistController@update');
            $router->post('show', 'V1\DirectRecruitmentApplicationChecklistController@show');
            $router->post('showBasedOnApplication', 'V1\DirectRecruitmentApplicationChecklistController@showBasedOnApplication');
        });

        /**
         * Routes for FWCMS.
         */
        $router->group(['prefix' => 'fwcms'], function () use ($router) {
            $router->post('list', 'V1\FWCMSController@list');
            $router->post('show', 'V1\FWCMSController@show');
            $router->post('create', 'V1\FWCMSController@create');
            $router->post('update', 'V1\FWCMSController@update');
        });
        /**
         * Routes for Levy.
         */
        $router->group(['prefix' => 'levy'], function () use ($router) {
            $router->post('list', 'V1\LevyController@list');
            $router->post('show', 'V1\LevyController@show');
            $router->post('create', 'V1\LevyController@create');
            $router->post('update', 'V1\LevyController@update');
        });

        /**
         * Routes for Application Interview.
         */
        $router->group(['prefix' => 'applicationInterview'], function () use ($router) {
            $router->post('list', 'V1\ApplicationInterviewController@list');
            $router->post('show', 'V1\ApplicationInterviewController@show');
            $router->post('create', 'V1\ApplicationInterviewController@create');
            $router->post('update', 'V1\ApplicationInterviewController@update');
            $router->post('deleteAttachment', 'V1\ApplicationInterviewController@deleteAttachment');
            $router->post('dropdownKsmReferenceNumber', 'V1\ApplicationInterviewController@dropdownKsmReferenceNumber');
        });

        /**
        * Routes for DirectRecruitmentApplicationApproval.
        */
        $router->group(['prefix' => 'directRecruitmentApplicationApproval'], function () use ($router) {
            $router->post('list', 'V1\DirectRecruitmentApplicationApprovalController@list');
            $router->post('show', 'V1\DirectRecruitmentApplicationApprovalController@show');
            $router->post('create', 'V1\DirectRecruitmentApplicationApprovalController@create');
            $router->post('update', 'V1\DirectRecruitmentApplicationApprovalController@update');
            $router->post('deleteAttachment', 'V1\DirectRecruitmentApplicationApprovalController@deleteAttachment');
        });
        /**
        * Routes for Application Summary.
        */
        $router->group(['prefix' => 'applicationSummary'], function () use ($router) {
            $router->post('list', 'V1\ApplicationSummaryController@list');
            $router->post('listKsmReferenceNumber', 'V1\ApplicationSummaryController@listKsmReferenceNumber');
        });

        /**
        * Routes for Application Summary.
        */
        $router->group(['prefix' => 'worker'], function () use ($router) {
            $router->post('list', 'V1\WorkersController@list');
            $router->post('show', 'V1\WorkersController@show');
            $router->post('create', 'V1\WorkersController@create');
            $router->post('update', 'V1\WorkersController@update');
            $router->post('export', 'V1\WorkersController@export');
            $router->post('dropdown', 'V1\WorkersController@dropdown');
            $router->post('updateStatus', 'V1\WorkersController@updateStatus');
            $router->post('kinRelationship', 'V1\WorkersController@kinRelationship');
            $router->post('onboardingAgent', 'V1\WorkersController@onboardingAgent');
            $router->post('replaceWorker', 'V1\WorkersController@replaceWorker');
            $router->post('workerStatusList', 'V1\WorkersController@workerStatusList');
        });
    });
});
