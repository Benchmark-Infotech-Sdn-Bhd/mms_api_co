<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\AgentServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AgentController extends Controller
{
    /**
     * @var AgentServices
     */
    private AgentServices $agentServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Class constructor. Initializes the object with the given AgentServices and AuthServices instances.
     *
     * @param AgentServices $agentServices The AgentServices instance to be used.
     * @param AuthServices $authServices The AuthServices instance to be used.
     *
     * @return void
     */
    public function __construct(AgentServices $agentServices, AuthServices $authServices)
    {
        $this->agentServices = $agentServices;
        $this->authServices = $authServices;
    }

    /**
     * Create a new record using the given request and return the result as a JsonResponse.
     *
     * @param Request $request The request object containing the data needed for creating the record.
     *
     * @return JsonResponse The response containing the result of the create operation.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $data = $this->agentServices->create($params);
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
     * Updates the record based on the given request.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response.
     *
     * @throws Exception If an error occurs during the update process.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->agentServices->update($params);
            if (isset($data['validate'])) {
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
     * Deletes a record using the given request.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response containing the result of the deletion.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->agentServices->delete($params);
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
     * Retrieves and returns agent data based on the given request.
     *
     * @param Request $request The HTTP request object containing the request parameters.
     *
     * @return JsonResponse The JSON response containing the agent data.
     *
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->agentServices->show($params);
            if (isset($data['validate'])) {
                return $this->validationError($data['validate']);
            } else if (is_null($data)) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Retrieves a list of agents based on the given request parameters.
     *
     * @param Request $request The Request object containing the request parameters.
     *
     * @return JsonResponse The JsonResponse object containing the list of agents, or an error message if an exception occurred.
     *
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->agentServices->list($params);
            if (isset($data['validate'])) {
                return $this->validationError($data['validate']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Updates the status using the provided request.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response.
     */
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->agentServices->updateStatus($params);
            if (isset($data['validate'])) {
                return $this->validationError($data['validate']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Retrieve dropdown data based on the given request.
     *
     * @param Request $request The request object containing the necessary parameters.
     *
     * @return JsonResponse The JSON response containing the dropdown data.
     */
    public function dropdown(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->agentServices->dropdown($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve All failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Updates the agent code using the provided request data.
     *
     * @param Request $request The HTTP request instance containing the agent code data.
     *
     * @return JsonResponse The HTTP response in JSON format.
     */
    public function updateAgentCode(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $data = $this->agentServices->updateAgentCode($params);
            if (isset($data['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
