<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\MaintainMastersServices;
use Illuminate\Support\Facades\Log;

class MaintainMastersController extends Controller
{
    /**
     * @var MaintainMastersServices
     */
    private MaintainMastersServices $maintainMastersServices;

    /**
     * MaintainMastersController constructor.
     * @param MaintainMastersServices $maintainMastersServices
     */
    public function __construct(MaintainMastersServices $maintainMastersServices)
    {
        $this->maintainMastersServices = $maintainMastersServices;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->maintainMastersServices->create($params);
            return response()->json(['result' => $this->sendResponse($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
