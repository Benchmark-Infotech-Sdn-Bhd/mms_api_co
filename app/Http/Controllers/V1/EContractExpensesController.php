<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\EContractExpensesServices;
use Illuminate\Support\Facades\Log;
use Exception;

class EContractExpensesController extends Controller
{
    /**
     * @var EContractExpensesServices
     */
    private EContractExpensesServices $eContractExpensesServices;

    /**
     * EContractExpensesController constructor.
     * @param EContractExpensesServices $eContractExpensesServices
     */
    public function __construct(EContractExpensesServices $eContractExpensesServices)
    {
        $this->eContractExpensesServices = $eContractExpensesServices;
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
            $data = $this->eContractExpensesServices->list($params);

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
            $data = $this->eContractExpensesServices->show($params);
            if(is_null($data) || count($data->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
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
            $data = $this->eContractExpensesServices->create($request);
            if(isset($data['unauthorizedError'])) {
                return $this->sendError($data['unauthorizedError']);
            }
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
            $data = $this->eContractExpensesServices->update($request);
            if(isset($data['unauthorizedError'])) {
                return $this->sendError($data['unauthorizedError']);
            }
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
            $data = $this->eContractExpensesServices->delete($params);
            if($data === false){
                return $this->sendError(['message' => 'No data found']);
            }
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
            $data = $this->eContractExpensesServices->deleteAttachment($params);
            if($data === false){
                return $this->sendError(['message' => 'No data found']);
            }
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
            $data = $this->eContractExpensesServices->payBack($params);
            if(isset($data['error'])){
                return $this->validationError($data['error']); 
            } else if(isset($data['payBackError'])) {
                return $this->validationError(['message' => 'Payback Amount Should not Exceed to Actual Amount'], 422); 
            } else if(isset($data['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']); 
            }
            return $this->sendSuccess(['message' => 'PayBack Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add PayBack']);
        }
    }
}
