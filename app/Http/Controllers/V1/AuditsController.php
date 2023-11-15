<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\AuditsServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class AuditsController extends Controller
{
    /**
     * @var AuditsServices
     */
    private $auditsServices;

    /**
     * AuditsController constructor.
     * @param AuditsServices $auditsServices
     */
    public function __construct(AuditsServices $auditsServices) 
    {
        $this->auditsServices = $auditsServices;
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
            $response = $this->auditsServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Audits']);
        }
    }

}