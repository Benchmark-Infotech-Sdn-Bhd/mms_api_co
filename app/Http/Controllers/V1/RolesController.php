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
            // $user = JWTAuth::parseToken()->authenticate();
            // app('logServices')->startApiLog($user, $params);
            $response = $this->rolesServices->getList(/*$user,*/ $params);
            // app('logServices')->endApiLog();
            return $this->sendSuccess(['data' => $response]);
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
            // $user = JWTAuth::parseToken()->authenticate();
            // app('logServices')->startApiLog($user, $params);
            $response = $this->rolesServices->show(/*$user,*/ $params);
            // app('logServices')->endApiLog();
            return $this->sendSuccess(['data' => $response]);
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
            // $user = JWTAuth::parseToken()->authenticate();
            // app('logServices')->startApiLog($user, $params);
            $response = $this->rolesServices->create(/*$user,*/ $params);
            // app('logServices')->endApiLog();
            return $this->sendSuccess(['message' => $response['message']]);
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
    public function update(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            // $user = JWTAuth::parseToken()->authenticate();
            // app('logServices')->startApiLog($user, $params);
            $response = $this->rolesServices->update(/*$user,*/ $params);
            // app('logServices')->endApiLog();
            return $this->sendSuccess(['message' => $response['message']]);
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
    public function delete(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            // $user = JWTAuth::parseToken()->authenticate();
            // app('logServices')->startApiLog($user, $params);
            $response = $this->rolesServices->delete(/*$user,*/ $params);
            // app('logServices')->endApiLog();
            return $this->sendSuccess(['message' => $response['message']]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Roles']);
        }
    }
}