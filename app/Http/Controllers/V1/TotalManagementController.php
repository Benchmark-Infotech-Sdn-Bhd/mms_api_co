<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementServices;
use Illuminate\Support\Facades\Log;
use Exception;

class TotalManagementController extends Controller
{
    /**
     * @var TotalManagementServices
     */
    private $totalManagementServices;

    /**
     * TotalManagementController constructor.
     * @param TotalManagementServices $totalManagementServices
     */
    public function __construct(TotalManagementServices $totalManagementServices)
    {
        $this->totalManagementServices = $totalManagementServices;
    }
    /** Display list of prospect in total management.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function applicationListing(Request $request): JsonResponse
    {
        try {
            $response = $this->totalManagementServices->applicationListing($request);
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
            $response = $this->totalManagementServices->addService($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Service Added Successfully']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Service']);
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
            $response = $this->totalManagementServices->getQuota($request);
            return $this->sendSuccess(['approvedQuota' => $response]);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Get Quota']);
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
            $response = $this->totalManagementServices->showProposal($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Proposal']);
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
            }
            return $this->sendSuccess(['message' => 'Proposal Submitted Successfully.']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Submit Proposal']);
        }
    }
}