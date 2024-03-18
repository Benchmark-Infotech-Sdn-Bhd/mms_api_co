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
     * Constructor method for the class.
     *
     * @param BranchServices $branchServices An instance of the BranchServices class.
     * @param EmployeeServices $employeeServices An instance of the EmployeeServices class.
     * @param AuthServices $authServices An instance of the AuthServices class.
     *
     * @return void
     */
    public function __construct(BranchServices $branchServices, EmployeeServices $employeeServices, AuthServices $authServices)
    {
        $this->branchServices = $branchServices;
        $this->employeeServices = $employeeServices;
        $this->authServices = $authServices;
    }

    /**
     * Create a new branch based on the given request.
     *
     * @param Request $request The request object containing the branch data.
     *
     * @return JsonResponse The success response if the branch was created successfully,
     *                     or an error message if the creation failed.
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
     * List method for retrieving branch data.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response containing the branch data.
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
     * Display the branch data.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the branch data.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $response = $this->branchServices->show($request);
            if (is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve branch Data was failed']);
        }
    }

    /**
     * Update method for the class.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response object.
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
     * Delete method for the class.
     *
     * @param Request $request The request object containing the necessary data.
     *
     * @return JsonResponse The JSON response containing the success or error message.
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
     * Retrieves the dropdown list of branches.
     *
     * @return JsonResponse Returns the success response with the dropdown list of branches if successful,
     *                     otherwise returns the error response.
     */
    public function dropDown(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $companyId = $this->authServices->getCompanyIds($user);
            $response = $this->branchServices->dropDown($companyId);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to List branches']);
        }
    }

    /**
     * Update the status of a branch and its related employees.
     *
     * @param Request $request The request object containing the input data.
     *
     * @return JsonResponse The JSON response containing the updated data or error message.
     */
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $validation = $this->branchServices->updateStatusValidation($params, ['id' => 'required', 'status' => 'required|regex:/^[0-1]+$/|max:1']);
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
