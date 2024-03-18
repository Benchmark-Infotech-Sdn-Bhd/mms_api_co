<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\AccessManagementServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class AccessManagementController extends Controller
{
    /**
     * @var AccessManagementServices
     */
    private AccessManagementServices $accessManagementServices;

    /**
     * Constructs a new instance of the class.
     *
     * @param AccessManagementServices $accessManagementServices An instance of the AccessManagementServices class.
     */
    public function __construct(AccessManagementServices $accessManagementServices)
    {
        $this->accessManagementServices = $accessManagementServices;
    }

    /**
     * Retrieves and returns the list of modules.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of modules.
     */
    public function list(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['user_type'] = $user['user_type'];
            $params['company_id'] = $user['company_id'];
            $response = $this->accessManagementServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Modules']);
        }
    }

    /**
     * Create a new role permission.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the result of the operation.
     */
    public function create(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $validator = Validator::make($params, $this->accessManagementServices->createValidation());
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            $response = $this->accessManagementServices->create($params);
            if(isset($response['roleError'])) {
                return $this->sendError(['message' => 'Role Permission Already Exists.']);
            } else if(isset($response['moduleError'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Role Permission Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create Role Permission']);
        }
    }

    /**
     * Updates the role permission.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response containing the result of the update operation.
     * @throws Exception If an error occurs during the update operation.
     */
    public function update(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $validator = Validator::make($params, $this->accessManagementServices->updateValidation());
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            $response = $this->accessManagementServices->update($params);
            if(isset($response['moduleError'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Role Permission Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Role Permission']);
        }
    }
}
