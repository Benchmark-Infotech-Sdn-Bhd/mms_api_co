<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\AuditsServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class AuditsController extends Controller
{
    /**
     * @var AuditsServices
     */
    private AuditsServices $auditsServices;

    /**
     * Constructor method for the class.
     *
     * @param AuditsServices $auditsServices The audits services instance.
     */
    public function __construct(AuditsServices $auditsServices)
    {
        $this->auditsServices = $auditsServices;
    }

    /**
     * List method for the class.
     *
     * @param Request $request The request instance.
     *
     * @return JsonResponse The JSON response containing the audit list or error message.
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->auditsServices->list($params);
            if (!empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Audits']);
        }
    }
}
