<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ApplicationInterviewsServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class ApplicationInterviewController extends Controller
{
    /**
     * @var ApplicationInterviewsServices
     */
    private $applicationInterviewsServices;

    /**
     * ApplicationInterviewController constructor.
     * @param ApplicationInterviewsServices $applicationInterviewsServices
     */
    public function __construct(ApplicationInterviewsServices $applicationInterviewsServices) 
    {
        $this->applicationInterviewsServices = $applicationInterviewsServices;
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
            $param = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $param['created_by'] = $user['id'];
            $response = $this->applicationInterviewsServices->create($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the FWCMS Quota'], 422);
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
            $param = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $param['modified_by'] = $user['id'];
            $response = $this->applicationInterviewsServices->update($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the FWCMS Quota'], 422);
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
            $response = $this->applicationInterviewsServices->deleteAttachment($params);
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
            $response = $this->applicationInterviewsServices->dropdownKsmReferenceNumber($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Ksm Reference Number'], 400);
        }        
    }
}
