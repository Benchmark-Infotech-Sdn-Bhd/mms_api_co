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
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
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
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->companyServices->create($params);
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
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified'] = $user['id'];
            $response = $this->companyServices->update($params);
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
            $response = $this->companyServices->updateCompanyId($params);
            return $this->sendSuccess(['message' => 'Company ID Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Company ID'], 400);
        }
    }
}
