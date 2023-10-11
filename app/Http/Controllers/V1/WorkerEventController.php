<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\WorkerEventServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class WorkerEventController extends Controller
{
    /**
     * @var WorkerEventServices
     */
    private $workerEventServices;

    /**
     * WorkerEventController constructor.
     * @param WorkerEventServices $workerEventServices
     */
    public function __construct(WorkerEventServices $workerEventServices) 
    {
        $this->workerEventServices = $workerEventServices;
    }
    /**
     * Dispaly all events for a worker.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->workerEventServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Event'], 400);
        }
    }
    /**
     * Dispaly form to create worker event.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request) : JsonResponse
    {
        try {
            $response = $this->workerEventServices->create($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Event Added Sussessfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Event'], 400);
        }
    }
    /**
     * Dispaly form to update worker event.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request) : JsonResponse
    {
        try {
            $response = $this->workerEventServices->update($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['maxIdError'])) {
                return $this->sendError(['message' => 'Sorry! Cannot Update the Past Events'], 422);
            }
            return $this->sendSuccess(['message' => 'Event Updated Sussessfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Event'], 400);
        }
    }
    /**
     * Dispaly the event for a worker.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->workerEventServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Event'], 400);
        }
    }
    /**
     * Delete attachment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->workerEventServices->deleteAttachment($params);
            if ($response == true) {
                return $this->sendSuccess(['message' => 'Attachment Deleted Sussessfully']);
            } else {
                return $this->sendError(['message' => 'Data Not Found'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Delete Attachment'], 400);
        }
    }
}
