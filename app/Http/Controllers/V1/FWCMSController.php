<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\FWCMSServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class FWCMSController extends Controller
{
    /**
     * @var FWCMSServices
     */
    private $fwcmsServices;

    /**
     * FWCMSController constructor.
     * @param FWCMSServices $fwcmsServices
     */
    public function __construct(FWCMSServices $fwcmsServices) 
    {
        $this->fwcmsServices = $fwcmsServices;
    }
    /**
     * Display a listing of the FWCMS Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse
    {
        try {
            $param = $this->getRequest($request);
            $response = $this->fwcmsServices->list($param);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List FWCMS Details']);
        }
    }
    /**
     * Display the FWCMS Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->fwcmsServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show FWCMS Details']);
        }
    }
    /**
     * Create the FWCMS Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        try{
            $param = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $param['created_by'] = $user['id'];
            $response = $this->fwcmsServices->create($param);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'FWCMS Details Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create FWCMS Details']);
        }
    }
    /**
     * Update the FWCMS Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $param = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $param['modified_by'] = $user['id'];
            $response = $this->fwcmsServices->update($param);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['processError'])) {
                return $this->sendError(['message' => 'Levy payment has been made for the selected KSM reference Number, further modification is not allowed']);
            }
            return $this->sendSuccess(['message' => 'FWCMS Details Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update FWCMS Details']);
        }
    }
}
