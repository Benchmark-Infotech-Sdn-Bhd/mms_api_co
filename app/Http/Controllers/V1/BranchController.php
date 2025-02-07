<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\BranchServices;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Services\EmployeeServices;
use App\Services\AuthServices;
use Tymon\JWTAuth\Facades\JWTAuth;

class BranchController extends Controller
{
    /**
     * @var branchServices
     */
    private BranchServices $branchServices;
    /**
     * @var employeeServices
     */
    private EmployeeServices $employeeServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * branchServices constructor.
     * @param BranchServices $branchServices
     * @param AuthServices $authServices
     */
    public function __construct(BranchServices $branchServices, EmployeeServices $employeeServices, AuthServices $authServices)
    {
        $this->branchServices = $branchServices;
        $this->employeeServices = $employeeServices;
        $this->authServices = $authServices;
    }
	 /**
     * Show the form for creating a new Branch.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        try {
            $validation = $this->branchServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->branchServices->create($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Branch creation was failed']);
        }
    }
	 /**
     * Display a listing of the Branch.
     * @param Request $request
     * @return JsonResponse
     */    
    public function list(Request $request): JsonResponse
    {     
        try {   
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->branchServices->list($params); 
            return $this->sendSuccess($response); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve all branch data was failed']);
        }
    }
	 /**
     * Display the data for edit form by using branch id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {   
        try {
            $response = $this->branchServices->show($request); 
            return $this->sendSuccess($response);  
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve branch Data was failed']);
        }  
    } 
	 /**
     * Update the specified branch data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {    
        try {    
            $validation = $this->branchServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->branchServices->update($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Branch update was failed']);
        }
    }
	 /**
     * delete the specified Branch data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {     
        try { 
            $params = $this->getRequest($request);
            $response = $this->branchServices->delete($params);
            return $this->sendSuccess($response); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete branch was failed']);
        } 
    }

    /**
     * Dropdown the branches
     * 
     * @return JsonResponse
     */
    public function dropDown(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $companyId = $this->authServices->getCompanyIds($user);
            $response = $this->branchServices->dropDown($companyId);
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to List branches']);
        }
    }

    /**
     * Update the branch active / inactive status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $validation = $this->branchServices->updateStatusValidation($params,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']);
            if ($validation) {
                return $this->validationError($validation);
            }
            $data = $this->branchServices->updateStatus($params);
            $this->employeeServices->updateStatusBasedOnBranch(['branch_id' => $request['id'], 'status' => $request['status']]);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }
    }
}
