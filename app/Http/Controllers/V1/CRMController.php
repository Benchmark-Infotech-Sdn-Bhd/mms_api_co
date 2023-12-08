<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\CRMServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Exception;

class CRMController extends Controller
{
    /**
     * @var CRMServices
     */
    private $crmServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * CRMController constructor.
     * @param CRMServices $crmServices
     * @param AuthServices $authServices
     */
    public function __construct(CRMServices $crmServices, AuthServices $authServices)
    {
        $this->crmServices = $crmServices;
        $this->authServices = $authServices;
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
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->crmServices->list($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
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
            $request['company_id'] = $user['company_id'];
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
    public function dropDownCompanies(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->crmServices->dropDownCompanies($params);
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
    /**
     * Get System List.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function systemList(Request $request): JsonResponse
    {
        try {
            $response = $this->crmServices->systemList();
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to List Systems']);
        }
    }
    /**
     * Get System List.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function crmImport(Request $request): JsonResponse
    {
        try {
            
            $originalFilename = $request->file('file')->getClientOriginalName();
            $originalFilename_arr = explode('.', $originalFilename);
            $fileExt = end($originalFilename_arr);
            $destinationPath = storage_path('upload/crm/');
            $fileName = 'A-' . time() . '.' . $fileExt;
            $request->file('file')->move($destinationPath, $fileName);
            
            $response = $this->crmServices->crmImport($request, $destinationPath . $fileName);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }

            return $this->sendSuccess(['message' => "Successfully crm was imported"]);

        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Import']);
        }
    }
}