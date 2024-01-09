<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CompanyServices;
use App\Services\AuthServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class CompanyController extends Controller
{
    /**
     * @var CompanyServices
     */
    private CompanyServices $companyServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * CompanyController constructor
     * @param CompanyServices $companyServices
     * @param AuthServices $authServices
     */
    public function __construct(CompanyServices $companyServices, AuthServices $authServices)
    {
        $this->companyServices = $companyServices;
        $this->authServices = $authServices;
    }
    /**
     * Display the list of Companies
     * 
     * @param Request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Companies'], 400);
        }
    }
    /**
     * Display a specific Company
     * 
     * @param Request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to display a company'], 400);
        }
    }
    /**
     * Create a Company
     * 
     * @param Request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['created_by'] = $user['id'];
            $response = $this->companyServices->create($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Company Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to create a Company'], 400);
        }
    }
    /**
     * Update a Company
     * 
     * @param Request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified'] = $user['id'];
            $response = $this->companyServices->update($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Company Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to update Company'], 400);
        }
    }
    /**
     * Update a Company status
     * 
     * @param Request
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified'] = $user['id'];
            $response = $this->companyServices->updateStatus($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Company Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to update Company Status'], 400);
        }
    }
    /**
     * Display the list of Companies
     * 
     * @param Request
     * @return JsonResponse
     */
    public function subsidiaryDropDown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->subsidiaryDropDown($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Companies'], 400);
        }
    }
    /**
     * Assign company as subsidiary
     * 
     * @param Request
     * @return JsonResponse
     */
    public function assignSubsidiary(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->companyServices->assignSubsidiary($params);
            return $this->sendSuccess(['message' => 'Subsidiary Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Subsidiary'], 400);
        }
    }
    /**
     * Display the list of Companies
     * 
     * @param Request
     * @return JsonResponse
     */
    public function parentDropDown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->parentDropDown($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Companies'], 400);
        }
    }
    /**
     * Display the list of Companies for the user
     * 
     * @param Request
     * @return JsonResponse
     */
    public function listUserCompany(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['user_id'] = $user['id'];
            $response = $this->companyServices->listUserCompany($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Companies'], 400);
        }
    }
     /**
     * Update a company id after login
     * 
     * @param Request
     * @return JsonResponse
     */
    public function updateCompanyId(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['user_id'] = $user['id'];
            $params['current_company_id'] = $user['company_id'];
            $response = $this->companyServices->updateCompanyId($params);
            if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']); 
            }
            return $this->sendSuccess(['message' => 'Company ID Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Company ID'], 400);
        }
    }
    /**
     * Display the list of subsidairy companies based on parent
     * 
     * @param Request
     * @return JsonResponse
     */
    public function subsidiaryDropdownBasedOnParent(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->subsidiaryDropdownBasedOnParent($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Companies'], 400);
        }
    }
    /**
     * Display the list of Companies
     * 
     * @param Request
     * @return JsonResponse
     */
    public function dropdown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->dropdown($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Companies'], 400);
        }
    }
    /**
     * Delete attachment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->deleteAttachment($params);
            if ($response == true) {
                return $this->sendSuccess(['message' => 'Attachment Deleted Sussessfully']);
            } else {
                return $this->sendError(['message' => 'Data Not Found'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Delete Attachment'], 400);
        }
    }
    /**
     * List Company Module.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function moduleList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->moduleList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Modules']);
        }
    }
    /**
     * Assign Module.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function assignModule(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->companyServices->assignModule($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Module Assigned Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Assign Module']);
        }
    }
    /**
     * Retrieves and returns the list of features owned by a company.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function featureList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $response = $this->companyServices->featureList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Features']);
        }
    }
    /**
     * Assign features to a particular company.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function assignFeature(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->companyServices->assignFeature($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Feature Assigned Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Assign Feature']);
        }
    }
    /**
     * List Account System Title.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function accountSystemTitleList(Request $request) : JsonResponse
    {
        try {
            $response = $this->companyServices->accountSystemTitleList();
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Account System Title']);
        }
    }
    /**
     * Show Account System.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function accountSystemShow(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->accountSystemShow($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Account System']);
        }
    }
    /**
     * Update Account System.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function accountSystemUpdate(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->companyServices->accountSystemUpdate($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }elseif (isset($response['InvalidTitle'])) {
                return $this->sendError(['message' => 'Invalid Title.']); 
            }
            return $this->sendSuccess(['message' => 'Account System Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update the Account System']);
        }
    }
    /**
     * Delete a Account System.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function accountSystemDelete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->companyServices->accountSystemDelete($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Account System Deleted Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Delete Account System']);
        }
    }
}
