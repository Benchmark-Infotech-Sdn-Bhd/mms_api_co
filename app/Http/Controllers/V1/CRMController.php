<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\CRMServices;
use Illuminate\Support\Facades\Log;
use Exception;

class CRMController extends Controller
{
    /**
     * @var CRMServices
     */
    private $crmServices;

    /**
     * CRMController constructor.
     * @param CRMServices $crmServices
     */
    public function __construct(CRMServices $crmServices)
    {
        $this->crmServices = $crmServices;
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
            $response = $this->crmServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Prospect']);
        }
    }
    /**
     * Display a resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->crmServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Prospect']);
        }
    }
    /**
     * Show the Form for creating crm prospect.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['created_by'] = $user['id'];
            $response = $this->crmServices->create($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Prospect Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create Prospect']);
        }
    }
    /**
     * Show the Form for updating crm prospect.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified'] = $user['id'];
            $response = $this->crmServices->update($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Prospect Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Prospect']);
        }
    }
    /**
     * Delete a resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->crmServices->deleteAttachment($params);
            return $this->sendSuccess(['message' => 'Prospect Attachment Deleted Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Delete Prospect Attachment']);
        }
    }
    /**
     * Listing the Companies.
     * 
     * @return JsonResponse
     */
    public function dropDownCompanies(): JsonResponse
    {
        try {
            $response = $this->crmServices->dropDownCompanies();
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to List Companies']);
        }
    }
     /**
     * Get Prospect Details.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getProspectDetails(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->crmServices->getProspectDetails($params);
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Get Prospect Details']);
        }
    }
}