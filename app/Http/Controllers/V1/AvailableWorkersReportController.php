<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AvailableWorkersReportServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AvailableWorkersReportController extends Controller
{
    /**
     * @var AvailableWorkersReportServices
     */
    private AvailableWorkersReportServices $availableWorkersReportServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Class constructor.
     *
     * @param AvailableWorkersReportServices $availableWorkersReportServices The available workers report services.
     * @param AuthServices $authServices The authentication services.
     *
     * @return void
     */
    public function __construct(AvailableWorkersReportServices $availableWorkersReportServices, AuthServices $authServices)
    {
        $this->availableWorkersReportServices = $availableWorkersReportServices;
        $this->authServices = $authServices;
    }

    /**
     * List available workers report.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The response object.
     *
     * @throws Exception If an error occurs.
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->availableWorkersReportServices->list($params);
            if (isset($response['validate'])) {
                return $this->validationError($response['validate']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Available Workers Report'], 400);
        }
    }

}
