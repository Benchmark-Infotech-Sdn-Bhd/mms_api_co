<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentImmigrationFeePaidServices;
use App\Services\AuthServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentImmigrationFeePaidController extends Controller
{
    /**
     * @var DirectRecruitmentImmigrationFeePaidServices
     */
    private $directRecruitmentImmigrationFeePaidServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentImmigrationFeePaidController constructor.
     * @param DirectRecruitmentImmigrationFeePaidServices $directRecruitmentImmigrationFeePaidServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentImmigrationFeePaidServices $directRecruitmentImmigrationFeePaidServices, AuthServices $authServices) 
    {
        $this->directRecruitmentImmigrationFeePaidServices = $directRecruitmentImmigrationFeePaidServices;
        $this->authServices = $authServices;
    }
    /**
     * Update Immigration Fee Paid
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $response = $this->directRecruitmentImmigrationFeePaidServices->update($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if($response == true) {
                return $this->sendSuccess(['message' => 'Immigration Fee Paid Updated Successfully']);
            } else {
                return $this->sendError(['message' => 'Failed to Update Immigration Fee Paid'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Immigration Fee Paid'], 400);
        }
    }
    /**
     * Display list of workers for particular calling visa reference number.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentImmigrationFeePaidServices->workersList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Display list of workers based on calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listBasedOnCallingVisa(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentImmigrationFeePaidServices->listBasedOnCallingVisa($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Display previous details based on calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentImmigrationFeePaidServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Details'], 400);
        }
    }
}
