<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\RolesServices;
use App\Services\AuthServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * RolesController constructor.
     * @param RolesServices $rolesServices
     * @param AuthServices $authServices
     */
    public function __construct(RolesServices $rolesServices, AuthServices $authServices) 
    {
        $this->rolesServices = $rolesServices;
        $this->authServices = $authServices;
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
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->rolesServices->list($params);
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
            $params['company_id'] = $user['company_id'];
            $params['user_type'] = $user['user_type'];
            $validator = Validator::make($params, $this->rolesServices->createValidation());
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            } 
            $response = $this->rolesServices->create($params);
            if(isset($response['adminError'])) {
                return $this->sendError(['message' => 'Role Name as Admin is not allowed, kindly provide a different Role Name.'],422);
            } else if(isset($response['superUserError'])) {
                return $this->sendError(['message' => 'Only Super User can Create Role with Speacial Permission'],422);
            } else if(isset($response['subsidiaryError'])) {
                return $this->sendError(['message' => 'Subsidiary Company cannot Create Role with Speacial Permission'],422);
            }
            return $this->sendSuccess(['message' => 'Role Created Successfully']);
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
            $this->rolesServices->update($params);
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
    public function delete(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $this->rolesServices->delete($params);
            return $this->sendSuccess(['message' => 'Role Deleted Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Delete Role']);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function dropDown(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $companyId = $this->authServices->getCompanyIds($user);
            $response = $this->rolesServices->dropDown($companyId);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Roles']);
        }
    }
    /**
     * Update Status for role.
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
            $validator = Validator::make($params, ['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']);
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }
            return $this->sendSuccess($this->rolesServices->updateStatus($params));
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}