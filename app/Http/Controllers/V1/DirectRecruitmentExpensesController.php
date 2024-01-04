<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\WorkersServices;
use App\Services\DirectRecruitmentExpensesServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class DirectRecruitmentExpensesController extends Controller
{
    /**
     * @var directRecruitmentExpensesServices
     */
    private DirectRecruitmentExpensesServices $directRecruitmentExpensesServices;

    /**
     * DirectRecruitmentExpensesController constructor.
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices
     */
    public function __construct(DirectRecruitmentExpensesServices $directRecruitmentExpensesServices)
    {
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
    }
    /**
     * Show the form for creating a new Expenses.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $this->directRecruitmentExpensesServices->create($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }else if(isset($data['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Show the form for creating a new Expenses.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            
            $data = $this->directRecruitmentExpensesServices->update($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }else if(isset($data['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    
    /**
     * Retrieve the specified Expenses.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->directRecruitmentExpensesServices->show($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }else if(is_null($data)){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    
    
    /**
     * Search & Retrieve all the Expenses.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->directRecruitmentExpensesServices->list($params);
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
     * Delete Expense Attachment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentExpensesServices->deleteAttachment($params);
            if ($response == true) {
                return $this->sendSuccess(['message' => 'Attachment Deleted Sussessfully']);
            } else {
                return $this->sendError(['message' => 'Data Not Found'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Delete Attachment']);
        }
    }   
}
