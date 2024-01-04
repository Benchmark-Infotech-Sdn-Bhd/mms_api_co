<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DirectRecruitmentApplicationChecklistServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;

class DirectRecruitmentApplicationChecklistController extends Controller
{
    /**
     * @var DirectRecruitmentApplicationChecklistServices
     */
    private DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentApplicationChecklistController constructor.
     * @param DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices, AuthServices $authServices)
    {
        $this->directRecruitmentApplicationChecklistServices = $directRecruitmentApplicationChecklistServices;
        $this->authServices = $authServices;
    }
    /**
     * Show the form for updating a DirectRecruitmentApplicationChecklist.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $data = $this->directRecruitmentApplicationChecklistServices->update($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }  
    /**
     * Retrieve the specified DirectRecruitmentApplicationChecklist.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->directRecruitmentApplicationChecklistServices->show($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            } else if(is_null($data)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Retrieve the specified DirectRecruitmentApplicationChecklist Based on Application.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function showBasedOnApplication(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->directRecruitmentApplicationChecklistServices->showBasedOnApplication($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            } else if(is_null($data)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
