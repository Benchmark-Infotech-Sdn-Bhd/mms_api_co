<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\ModulesServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    /**
     * @var ModulesServices
     */
    private $modulesServices;

    /**
     * ModulesController constructor.
     * @param ModulesServices $modulesServices
     */
    public function __construct(ModulesServices $modulesServices) 
    {
        $this->modulesServices = $modulesServices;
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