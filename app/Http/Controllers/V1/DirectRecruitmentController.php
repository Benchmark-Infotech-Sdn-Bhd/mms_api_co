<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentController extends Controller
{
    /**
     * @var DirectRecruitmentServices
     */
    private $directRecruitmentServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentController constructor.
     * @param DirectRecruitmentServices $directRecruitmentServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentServices $directRecruitmentServices, AuthServices $authServices)
    {
        $this->directRecruitmentServices = $directRecruitmentServices;
        $this->authServices = $authServices;
    }
    /**
     * Show the form for creating a new Proposal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitProposal(Request $request): JsonResponse
    {     
        try {   
            $validation = $this->directRecruitmentServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->directRecruitmentServices->submitProposal($request); 
            if(isset($response['error'])) {
                return $this->sendError(['message' => 'Please finish the previous process before submitting a new application as this company service is still in progress.']);
            }      
            return $this->sendSuccess($response);
            
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }
    }
    /**
     * Display the data for edit form by using proposal id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function showProposal(Request $request): JsonResponse
    {     
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentServices->showProposal($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }
    }
    /*** Add a services to the prospect.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function addService(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['created_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentServices->addService($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Service Added Successfully']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Service']);
        }
    }
    /**
     * Listing Prospect services.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function applicationListing(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentServices->applicationListing($params);
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Services']);
        }
    }
    /**
     * delete the specified Attachment data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request): JsonResponse
    {   
        try {
            $response = $this->directRecruitmentServices->deleteAttachment($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }        
    }
    /**
     * Listing Prospect services.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function dropDownFilter(Request $request): JsonResponse
    {
        try {
            $response = $this->directRecruitmentServices->dropDownFilter();
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Filters'], 400);
        }
    }
}