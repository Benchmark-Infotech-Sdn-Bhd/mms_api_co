<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\RolesServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class RolesController extends Controller
{
    /**
     * @var RolesServices
     */
    private $rolesServices;

    /**
     * RolesController constructor.
     * @param RolesServices $rolesServices
     */
    public function __construct(RolesServices $rolesServices) 
    {
        $this->rolesServices = $rolesServices;
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
            $response = $this->rolesServices->list();
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Roles']);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $response = $this->rolesServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Role']);
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
            $validator = Validator::make($params, $this->rolesServices->createValidation());
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            $response = $this->rolesServices->create($params);
            return $this->sendSuccess(['message' => 'Rloe Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create Role']);
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
            $validator = Validator::make($params, $this->rolesServices->updateValidation());
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            $response = $this->rolesServices->update($params);
            return $this->sendSuccess(['message' => 'Role Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Role']);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->rolesServices->updateStatus($params);
            return $this->sendSuccess(['message' => 'Role Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Role']);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dropDown(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $response = $this->rolesServices->dropDown();
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Roles']);
        }
    }
}