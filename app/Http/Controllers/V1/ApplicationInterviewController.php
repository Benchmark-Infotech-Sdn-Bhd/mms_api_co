<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ApplicationInterviewsServices;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Services\AuthServices;
use Exception;

class ApplicationInterviewController extends Controller
{
    /**
     * @var ApplicationInterviewsServices
     */
    private ApplicationInterviewsServices $applicationInterviewsServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Initializes a new instance of the class.
     *
     * @param ApplicationInterviewsServices $applicationInterviewsServices The ApplicationInterviewsServices object.
     * @param AuthServices $authServices The AuthServices object.
     *
     * @return void
     */
    public function __construct(ApplicationInterviewsServices $applicationInterviewsServices, AuthServices $authServices)
    {
        $this->applicationInterviewsServices = $applicationInterviewsServices;
        $this->authServices = $authServices;
    }

    /**
     * List the application interview details.
     *
     * @param Request $request The Request object.
     *
     * @return JsonResponse The JSON response containing the list of application interview details.
     */
    public function list(Request $request): JsonResponse
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
     * Display the application interview details.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response object.
     */
    public function show(Request $request): JsonResponse
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
     * Creates a new application interview details.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The response containing the result of the creation process.
     */
    public function create(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['created_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $response = $this->applicationInterviewsServices->create($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the FWCMS Quota'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Application Interview Details Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create Application Interview Details']);
        }
    }

    /**
     * Updates the application interview details.
     *
     * @param Request $request The Request object containing the updated details.
     *
     * @return JsonResponse The JSON response indicating the success or failure of the update.
     *
     * @throws JWTException If an error occurs while parsing the JWT token.
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
            } else if (isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the FWCMS Quota'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Application Interview Details Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Application Interview Details']);
        }
    }

    /**
     * Delete an attachment.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response.
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
            if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete attachments was failed']);
        }
    }

    /**
     * Retrieves the dropdown list of KSM Reference Numbers.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the dropdown list of KSM Reference Numbers.
     */
    public function dropdownKsmReferenceNumber(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->applicationInterviewsServices->dropdownKsmReferenceNumber($params);
            if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Ksm Reference Number']);
        }
    }
}
