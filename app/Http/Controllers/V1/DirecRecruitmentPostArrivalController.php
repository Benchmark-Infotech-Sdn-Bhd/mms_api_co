<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirecRecruitmentPostArrivalServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirecRecruitmentPostArrivalController extends Controller
{
    /**
     * @var DirecRecruitmentPostArrivalServices
     */
    private $direcRecruitmentPostArrivalServices;

    /**
     * DirecRecruitmentPostArrivalController constructor.
     * @param DirecRecruitmentPostArrivalServices $direcRecruitmentPostArrivalServices
     */
    public function __construct(DirecRecruitmentPostArrivalServices $direcRecruitmentPostArrivalServices) 
    {
        $this->direcRecruitmentPostArrivalServices = $direcRecruitmentPostArrivalServices;
    }
    /**
     * Update post arrival status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePostArrivalStatus(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->direcRecruitmentPostArrivalServices->updatePostArrivalStatus($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Post Arrival Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Post Arrival'], 400);
        }
    }
}
