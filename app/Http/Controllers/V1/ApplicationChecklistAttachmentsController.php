<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ApplicationChecklistAttachmentsServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;

class ApplicationChecklistAttachmentsController extends Controller
{
    /**
     * @var ApplicationChecklistAttachmentsServices
     */
    private ApplicationChecklistAttachmentsServices $applicationChecklistAttachmentsServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Constructs a new instance of the class.
     *
     * @param ApplicationChecklistAttachmentsServices $applicationChecklistAttachmentsServices The application checklist attachments services.
     * @param AuthServices $authServices The auth services.
     *
     * @return void
     */
    public function __construct(ApplicationChecklistAttachmentsServices $applicationChecklistAttachmentsServices, AuthServices $authServices)
    {
        $this->applicationChecklistAttachmentsServices = $applicationChecklistAttachmentsServices;
        $this->authServices = $authServices;
    }

    /**
     * Create a new checklist attachment.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The response object.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['created_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $data = $this->applicationChecklistAttachmentsServices->create($request);
            if (isset($data['validate'])) {
                return $this->validationError($data['validate']);
            } else if (isset($data['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Delete a record.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response indicating the success or failure of the deletion.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $data = $this->applicationChecklistAttachmentsServices->delete($params);
            if (isset($data['validate'])) {
                return $this->validationError($data['validate']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Deletion failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Responds with a JSON response containing a list of application checklist attachments.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response.
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->applicationChecklistAttachmentsServices->list($params);
            if (isset($data['validate'])) {
                return $this->validationError($data['validate']);
            } else if (isset($data['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
