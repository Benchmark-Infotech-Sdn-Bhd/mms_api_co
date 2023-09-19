<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementPayrollServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class TotalManagementPayrollController extends Controller
{
    /**
     * @var TotalManagementPayrollServices
     */
    private $totalManagementPayrollServices;

    /**
     * TotalManagementPayrollController constructor.
     * @param TotalManagementPayrollServices $totalManagementPayrollServices
     */
    public function __construct(TotalManagementPayrollServices $totalManagementPayrollServices)
    {
        $this->totalManagementPayrollServices = $totalManagementPayrollServices;
    }
    /**
     * Display the Total Management Payroll
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function projectDetails(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementPayrollServices->projectDetails($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Project Details'], 400);
        }
    }
    /**
     * Display list of Payroll
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementPayrollServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Total Management Payroll'], 400);
        }
    }
    /**
     * Export Payroll
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementPayrollServices->export($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Export Total Management Payroll'], 400);
        }
    }
    /**
     * Display the Total Management Payroll
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementPayrollServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Total Management Payroll'], 400);
        }
    }
    /**
     * import Payroll
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $originalFilename = $request->file('payroll_file')->getClientOriginalName();
            $originalFilename_arr = explode('.', $originalFilename);
            $fileExt = end($originalFilename_arr);
            $destinationPath = storage_path('upload/payroll/');
            $fileName = 'A-' . time() . '.' . $fileExt;
            $request->file('payroll_file')->move($destinationPath, $fileName);
            $data = $this->totalManagementPayrollServices->import($request, $destinationPath . $fileName);
            if(isset($data['error'])){
                return $this->validationError($data['error']); 
            }
            return $this->sendSuccess(['message' => "Successfully payroll was imported"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Import failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }
    /**
     * Add Payroll
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->totalManagementPayrollServices->add($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if($response == false){
                return $this->sendError(['message' => 'Failed to Add Total Management Payroll due to already data exists'], 422);
            }
            return $this->sendSuccess(['message' => 'Total Manangement Payroll Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Total Management Payroll'], 400);
        }
    }
    /**
     * Update Project
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->totalManagementPayrollServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Total Management Payroll Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Total Management Payroll'], 400);
        }
    }
    /**
     * Display list of Payroll Timesheet
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function listTimesheet(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementPayrollServices->listTimesheet($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Total Management Payroll Timesheet'], 400);
        }
    }
    /**
     * view Timesheet
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function viewTimesheet(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementPayrollServices->viewTimesheet($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Total Management Payroll Timesheet'], 400);
        }
    }
    /**
     * Upload Timesheet
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function uploadTimesheet(Request $request): JsonResponse
    {
        try {
            $response = $this->totalManagementPayrollServices->uploadTimesheet($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }if(isset($response['existsError'])) {
                return $this->sendError(['message' => 'Failed to Upload Total Management Payroll Timesheet due to Timesheet exists for this month'], 422);
            }elseif($response == false) {
                return $this->sendError(['message' => 'Failed to Upload Total Management Payroll Timesheet'], 422);
            }
            return $this->sendSuccess(['message' => 'Total Management Payroll Timesheet Uploaded Sucessfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Upload Total Management Payroll Timesheet'], 400);
        }
    }
    /**
     * Authorize Payroll
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function authorizePayroll(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementPayrollServices->authorizePayroll($params);
            if(isset($response['existsError'])) {
                return $this->sendError(['message' => 'Failed to Upload Total Management Payroll to Expenses Due to Expense Exists for This Month'], 422);
            } else if(isset($response['noRecords'])) {
                return $this->sendError(['message' => 'No Records Found to Update E-Contract Payroll to Expenses'], 422);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Authorize Payroll'], 400);
        }
    }

}