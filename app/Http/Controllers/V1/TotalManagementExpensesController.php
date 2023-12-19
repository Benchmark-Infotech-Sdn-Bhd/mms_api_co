<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementExpensesServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;
use Exception;

class TotalManagementExpensesController extends Controller
{
    /**
     * @var totalManagementExpensesServices
     */
    private TotalManagementExpensesServices $totalManagementExpensesServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * TotalManagementExpensesController constructor.
     * @param TotalManagementExpensesServices $totalManagementExpensesServices
     * @param AuthServices $authServices
     */
    public function __construct(TotalManagementExpensesServices $totalManagementExpensesServices, AuthServices $authServices)
    {
        $this->totalManagementExpensesServices = $totalManagementExpensesServices;
        $this->authServices = $authServices;
    }
     /**
     * Expense list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementExpensesServices->list($params);
            if(isset($data['error'])) {
                return $this->validationError($data['error']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Expense'], 400);
        }
    }
    /**
     * Retrieve the Expense
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->totalManagementExpensesServices->show($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Expense'], 400);
        }
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
            $data = $this->totalManagementExpensesServices->create($request);
            if(isset($data['error'])) {
                return $this->validationError($data['error']);
            }
            return $this->sendSuccess(['message' => 'Expense Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create Expense']);
        }
    }
    /**
     * Show the form for update Expense.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $data = $this->totalManagementExpensesServices->update($request);
            if(isset($data['error'])){
                return $this->validationError($data['error']); 
            }
            return $this->sendSuccess(['message' => 'Expense Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Expense']);
        }
    }
    /**
     * Delete Expense.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $this->totalManagementExpensesServices->delete($params);
            return $this->sendSuccess(['message' => 'Expense Deleted Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Delete Expense']);
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
            $this->totalManagementExpensesServices->deleteAttachment($params);
            return $this->sendSuccess(['message' => 'Attachment Deleted Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Delete Attachment']);
        }
    }
    /**
     * Show the form for payback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function payBack(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->totalManagementExpensesServices->payBack($params);
            if(isset($data['error'])){
                return $this->validationError($data['error']); 
            } else if(isset($data['payBackError'])) {
                return $this->validationError(['message' => 'Payback Amount Should not Exceed to Actual Amount'], 422); 
            }
            return $this->sendSuccess(['message' => 'PayBack Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add PayBack']);
        }
    }
}
