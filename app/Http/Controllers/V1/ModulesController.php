<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\ModulesServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\AuthServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    /**
     * @var ModulesServices
     */
    private $modulesServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * ModulesController constructor.
     * @param ModulesServices $modulesServices
     * @param AuthServices $authServices
     */
    public function __construct(ModulesServices $modulesServices, AuthServices $authServices) 
    {
        $this->modulesServices = $modulesServices;
        $this->authServices = $authServices;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function dropDown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['user_type'] = $user['user_type'];
            $params['company_id'] = $user['company_id'];
            $response = $this->modulesServices->dropDown($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Modules']);
        }
    }
}