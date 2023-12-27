<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ApplicationInterviewsServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Services\AuthServices;
use Exception;

class ApplicationInterviewController extends Controller
{
    /**
     * @var ApplicationInterviewsServices
     */
    private $applicationInterviewsServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * ApplicationInterviewController constructor.
     * @param ApplicationInterviewsServices $applicationInterviewsServices
     * @param AuthServices $authServices
     */
    public function __construct(ApplicationInterviewsServices $applicationInterviewsServices, AuthServices $authServices) 
    {
        $this->applicationInterviewsServices = $applicationInterviewsServices;
        $this->authServices = $authServices;
    }
    /**
     * Display a listing of the application Interview Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse
    {
        try {
            $param = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $param['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->applicationInterviewsServices->list($param);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Application Interview Details']);
        }
    }
    /**
     * Display the application Interview Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->applicationInterviewsServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Application Interview Details']);
        }
    }
    /**
     * Create the application Interview Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        try{
            $user = JWTAuth::parseToken()->authenticate();
            $request['created_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $response = $this->applicationInterviewsServices->create($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the FWCMS Quota'], 422);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Application Interview Details Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create Application Interview Details'], 400);
        }
    }
    /**
     * Update the application Interview Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $response = $this->applicationInterviewsServices->update($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the FWCMS Quota'], 422);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Application Interview Details Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Application Interview Details'], 400);
        }
    }

    /**
     * delete the specified Attachment data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request): JsonResponse
    {   
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->applicationInterviewsServices->deleteAttachment($params);
            if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete attachments was failed'], 400);
        }        
    }

    /**
     * List the Ksm Reference Number data for dropdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dropdownKsmReferenceNumber(Request $request): JsonResponse
    {   
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->applicationInterviewsServices->dropdownKsmReferenceNumber($params);
            if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Ksm Reference Number'], 400);
        }        
    }
}
