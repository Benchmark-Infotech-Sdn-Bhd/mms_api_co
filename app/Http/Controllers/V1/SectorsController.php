<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\SectorsServices;
use Illuminate\Support\Facades\Log;

class SectorsController extends Controller
{
    /**
     * @var SectorsServices
     */
    private SectorsServices $sectorsServices;

    /**
     * SectorsController constructor.
     * @param SectorsServices $sectorsServices
     */
    public function __construct(SectorsServices $sectorsServices)
    {
        $this->sectorsServices = $sectorsServices;
    }
    /**
     * Show the form for creating a new Sector.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->sectorsServices->create($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Show the form for updating a Sector.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->sectorsServices->update($params);
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
     * Remove the specified sector.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->sectorsServices->delete($params);
            if(isset($data['validate'])){
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
     * Retrieve the specified sector.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieve(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->sectorsServices->retrieve($params);
            if(isset($data['validate'])){
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
     * Retrieve all sectors.
     *
     * @return JsonResponse
     */
    public function retrieveAll(): JsonResponse
    {
        try {
            $data = $this->sectorsServices->retrieveAll();
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve All failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Update the checklist status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateChecklistStatus(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->sectorsServices->updateChecklistStatus($params);
            if(isset($data['validate'])){
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
     * Search & Retrieve all the sectors based on sector name.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchSectors(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->sectorsServices->searchSectors($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
