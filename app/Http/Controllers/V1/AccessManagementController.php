<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\AccessManagementServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class AccessManagementController extends Controller
{
    /**
     * @var AccessManagementServices
     */
    private $accessManagementServices;

    /**
     * AccessManagementController constructor.
     * @param AccessManagementServices $accessManagementServices
     */
    public function __construct(AccessManagementServices $accessManagementServices) 
    {
        $this->accessManagementServices = $accessManagementServices;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $response = $this->accessManagementServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Modules']);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $validator = Validator::make($params, $this->accessManagementServices->createValidation());
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            $response = $this->accessManagementServices->create($params);
            return $this->sendSuccess(['message' => 'Rloe Permission Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create Role Permission']);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $validator = Validator::make($params, $this->accessManagementServices->updateValidation());
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            $response = $this->accessManagementServices->update($params);
            return $this->sendSuccess(['message' => 'Role Permission Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Role Permission']);
        }
    }
}