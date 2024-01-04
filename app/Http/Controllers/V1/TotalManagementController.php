<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class TotalManagementController extends Controller
{
    /**
     * @var TotalManagementServices
     */
    private $totalManagementServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * TotalManagementController constructor.
     * @param TotalManagementServices $totalManagementServices
     * @param AuthServices $authServices
     */
    public function __construct(TotalManagementServices $totalManagementServices, AuthServices $authServices)
    {
        $this->totalManagementServices = $totalManagementServices;
        $this->authServices = $authServices;
    }
    /** Display list of prospect in total management.
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
            $response = $this->totalManagementServices->applicationListing($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Prospect']);
        }
    }
    /** Add a services to the prospect.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function addService(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementServices->addService($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                $this->sendError(['message' => 'Quota for service should not exceed to Initail quota'], 422);
            }
            return $this->sendSuccess(['message' => 'Service Added Successfully']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Service'], 400);
        }
    }
    /** Get approved quota for particular prospect.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getQuota(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->totalManagementServices->getQuota($params);
            return $this->sendSuccess(['approvedQuota' => $response]);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Get Quota'], 400);
        }
    }
    /** Display list of prospect for proposal.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function showProposal(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->totalManagementServices->showProposal($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Proposal'], 400);
        }
    }
    /** Display form to submit proposal.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function submitProposal(Request $request): JsonResponse
    {
        try {
            $response = $this->totalManagementServices->submitProposal($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->validationError(['message' => 'Quota for service should not exceed to Initail quota']);
            }else if(isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess(['message' => 'Proposal Submitted Successfully.']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Submit Proposal'], 400);
        }
    }
    /** Display form to allocate quota.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function allocateQuota(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->totalManagementServices->allocateQuota($params);
            if(isset($response['quotaError'])) {
                return $this->validationError(['message' => 'Quota for service should not exceed to Initail quota']);
            }else if(isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess(['message' => 'Quota Allocated Successfully.']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Allocate Quota'], 400);
        }
    }
    /** Display prospect service.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function showService(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->totalManagementServices->showService($params);
            if(is_null($response)){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Service'], 400);
        }
    }
}