<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\ModulesServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

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
    public function dropDown(): JsonResponse
    {
        try {
            $response = $this->modulesServices->dropDown();
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Modules']);
        }
    }
}