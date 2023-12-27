<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\EContractServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class EContractController extends Controller
{
    /**
     * @var EContractServices
     */
    private $eContractServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * EContractController constructor.
     * @param EContractServices $eContractServices
     * @param AuthServices $authServices
     */
    public function __construct(EContractServices $eContractServices, AuthServices $authServices)
    {
        $this->eContractServices = $eContractServices;
        $this->authServices = $authServices;
    }
    /** Display list of services in e-Contract.
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
            $response = $this->eContractServices->applicationListing($params);
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
            $user = JWTAuth::parseToken()->authenticate();
            $request['company_ids'] = $this->authServices->getCompanyIds($user);
            $request['company_id'] = $user['company_id'];
            $request['created_by'] = $user['id'];
            $response = $this->eContractServices->addService($request);
            if (isset($response['unauthorizedError'])) {
                return $this->sendError($response['unauthorizedError']);
            }
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Service Added Successfully']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Service'], 400);
        }
    }
    /** Display form to submit proposal.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function proposalSubmit(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            //$request['company_id'] = $this->authServices->getCompanyIds($user);
            $request['created_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $response = $this->eContractServices->submitProposal($request);
            if (isset($response['unauthorizedError'])) {
                return $this->sendError($response['unauthorizedError']);
            }
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Proposal Submitted Successfully.']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Submit Proposal'], 400);
        }
    }
    /** Display proposal Details.
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
            $response = $this->eContractServices->showProposal($params);
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Proposal'], 400);
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
            $response = $this->eContractServices->allocateQuota($params);
            if (isset($response['unauthorizedError'])) {
                return $this->sendError($response['unauthorizedError']);
            }
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Quota Updated Successfully.']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Get Quota'], 400);
        }
    }
    /** Display Service Details.
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
            $response = $this->eContractServices->showService($params);
            //dd($response->toArray()); exit;
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Service'], 400);
        }
    }
}
