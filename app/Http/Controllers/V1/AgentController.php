<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\AgentServices;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    /**
     * @var AgentServices
     */
    private AgentServices $agentServices;

    /**
     * AgentController constructor.
     * @param AgentServices $agentServices
     */
    public function __construct(AgentServices $agentServices)
    {
        $this->agentServices = $agentServices;
    }
    /**
     * Show the form for creating a new agent.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->agentServices->create($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Show the form for updating a agent.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->agentServices->update($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Remove the specified agent.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->agentServices->delete($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Deletion failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Retrieve the specified agent.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieve(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->agentServices->retrieve($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Retrieve all agents.
     *
     * @return JsonResponse
     */
    public function retrieveAll(): JsonResponse
    {
        try {
            $data = $this->agentServices->retrieveAll();
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve All failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Retrieve all agents by country.
     *
     * @return JsonResponse
     */
    public function retrieveByCountry(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->agentServices->retrieveByCountry($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve By Country failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
