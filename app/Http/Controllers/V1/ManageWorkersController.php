<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ManageWorkersServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class ManageWorkersController extends Controller
{
    /**
     * @var workersServices
     */
    private WorkersServices $workersServices;

    /**
     * ManageWorkersController constructor.
     * @param ManageWorkersServices $manageWorkersServices
     */
    public function __construct(ManageWorkersServices $manageWorkersServices)
    {
        $this->manageWorkersServices = $manageWorkersServices;
    }

    /**
     * Export Template for Workers Import from Excel.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportTemplate(Request $request): JsonResponse
    {
        try {            
            $data = $this->manageWorkersServices->exportTemplate($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }

            return $this->sendSuccess(['file_url' => $data, 'message' => "Successfully worker template was exported"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Export failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }

    }

}
