<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\DirectRecruitmentServices;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentController extends Controller
{
    /**
     * @var DirectRecruitmentServices
     */
    private $directRecruitmentServices;

    /**
     * DirectRecruitmentController constructor.
     * @param DirectRecruitmentServices $directRecruitmentServices
     */
    public function __construct(DirectRecruitmentServices $directRecruitmentServices)
    {
        $this->directRecruitmentServices = $directRecruitmentServices;
    }
    /**
     * Show the form for creating a new Proposal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createProposal(Request $request): JsonResponse
    {     
        try {   
            $validation = $this->directRecruitmentServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->directRecruitmentServices->addProposal($request);       
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
    /**
     * Update the specified Proposal data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProposal(Request $request): JsonResponse
    {  
        try {
            $validation = $this->directRecruitmentServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->directRecruitmentServices->updateProposal($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
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
}