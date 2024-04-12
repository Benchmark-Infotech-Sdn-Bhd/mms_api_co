<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationController extends Controller
{
    /**
     * @var NotificationServices
     */
    private $notificationServices;

    /**
     * NotificationController constructor.
     * @param NotificationServices $notificationServices
     */
    public function __construct(NotificationServices $notificationServices)
    {
        $this->notificationServices = $notificationServices;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function count(Request $request): JsonResponse
    {
        $params = $this->getRequest($request);
        $user = JWTAuth::parseToken()->authenticate();
        $data = $this->notificationServices->getcount($user);
        return $this->sendSuccess(['data' => $data]);
    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $data = $this->notificationServices->list($user);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }

    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateReadStatus(Request $request): JsonResponse
    {
        try {
            $data = $this->notificationServices->updateReadStatus($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Update failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function renewalNotifications(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->notificationServices->renewalNotifications($params['renewalType'], $params['frequency']);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Update failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }
}
