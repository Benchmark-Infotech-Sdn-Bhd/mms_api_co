<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\EContractPayrollServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class EContractPayrollController extends Controller
{
    /**
     * @var EContractPayrollServices
     */
    private $eContractPayrollServices;

    /**
     * EContractPayrollController constructor.
     * @param EContractPayrollServices $eContractPayrollServices
     */
    public function __construct(EContractPayrollServices $eContractPayrollServices)
    {
        $this->eContractPayrollServices = $eContractPayrollServices;
    }
    /**
     * Display the E-Contract Payroll
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function projectDetails(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->eContractPayrollServices->projectDetails($params);
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
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
            $response = $this->eContractPayrollServices->list($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List E-Contract Payroll'], 400);
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
            $response = $this->eContractPayrollServices->export($params);
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Export E-Contract Payroll'], 400);
        }
    }
    /**
     * Display the E-Contract Payroll
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->eContractPayrollServices->show($params);
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display E-Contract Payroll'], 400);
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
            $destinationPath = storage_path('upload/eContract/payroll/');
            $fileName = 'A-' . time() . '.' . $fileExt;
            $request->file('payroll_file')->move($destinationPath, $fileName);
            $data = $this->eContractPayrollServices->import($request, $destinationPath . $fileName);
            if(isset($data['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
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
            $response = $this->eContractPayrollServices->add($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if($response == false){
                return $this->sendError(['message' => 'Failed to Add E-Contract Payroll due to already data exists'], 422);
            }
            return $this->sendSuccess(['message' => 'E-Contract Payroll Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add E-Contract Payroll'], 400);
        }
    }
    /**
     * Update Payroll
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
            $response = $this->eContractPayrollServices->update($params);
            if(isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'E-Contract Payroll Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update E-Contract Payroll'], 400);
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
            $response = $this->eContractPayrollServices->listTimesheet($params);
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List E-Contract Payroll Timesheet'], 400);
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
            $response = $this->eContractPayrollServices->viewTimesheet($params);
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display E-Contract Payroll Timesheet'], 400);
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
            $response = $this->eContractPayrollServices->uploadTimesheet($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }if(isset($response['existsError'])) {
                return $this->sendError(['message' => 'Failed to Upload E-Contract Payroll Timesheet due to Timesheet exists for this month'], 422);
            }elseif($response == false) {
                return $this->sendError(['message' => 'Failed to Upload E-Contract Payroll Timesheet'], 422);
            }elseif(isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess(['message' => 'E-Contract Payroll Timesheet Uploaded Sucessfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Upload E-Contract Payroll Timesheet'], 400);
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
            $response = $this->eContractPayrollServices->authorizePayroll($params);
            if(isset($response['existsError'])) {
                return $this->sendError(['message' => 'Failed to Upload E-Contract Payroll to Expenses Due to Expense Exists for This Month'], 422);
            }
            if(isset($response['existsError'])) {
                return $this->sendError(['message' => 'Failed to Upload E-Contract Payroll to Expenses Due to Expense Exists for This Month'], 422);
            }  else if(isset($response['noRecords'])) {
                return $this->sendError(['message' => 'No Records Found to Update E-Contract Payroll to Expenses'], 422);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Authorize Payroll'], 400);
        }
    }
}