<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentServices;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentController extends Controller
{
    /**
     * @var DirectRecruitmentServices
     */
    private $directRecruitmentServices;

    /**
     * DirectRecruitmentController constructor.
     * @param DirectRecruitmentServices $directRecruitmentServices
     */
    public function __construct(DirectRecruitmentServices $directRecruitmentServices)
    {
        $this->directRecruitmentServices = $directRecruitmentServices;
    }
    /**
     * Listing the companies.
     * 
     * @return JsonResponse
     */
    public function addService(): JsonResponse
    {
        try {
            $response = $this->directRecruitmentServices->addService();
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Service']);
        }
    }
}
