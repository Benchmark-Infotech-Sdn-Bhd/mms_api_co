<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\LevyServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class LevyController extends Controller
{
    /**
     * @var LevyServices
     */
    private $levyServices; 
    /**
     * LevyController constructor.
     */
    public function __construct(LevyServices $levyServices)
    {
        $this->levyServices = $levyServices;
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->levyServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Levy Details']);
        }
        
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try{
            $params = $this->getRequest($request);
            $response = $this->levyServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage()), true);
            return $this->sendError(['message' => 'Failed to Show Levy Details']);
        }
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try{
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->levyServices->create($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Levy Details Created SUccessfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage()), true);
            return $this->sendError(['message' => 'Failed to Create Levy Details']);
        }
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try{
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->levyServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Levy Details Updated SUccessfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage()), true);
            return $this->sendError(['message' => 'Failed to Update Levy Details']);
        }
    }
}
