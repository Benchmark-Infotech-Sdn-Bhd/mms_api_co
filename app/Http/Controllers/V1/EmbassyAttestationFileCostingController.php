<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\EmbassyAttestationFileCostingServices;
use Illuminate\Support\Facades\Log;

class EmbassyAttestationFileCostingController extends Controller
{
    /**
     * @var EmbassyAttestationFileCostingServices
     */
    private EmbassyAttestationFileCostingServices $embassyAttestationFileCostingServices;

    /**
     * EmbassyAttestationFileCostingController constructor.
     * @param EmbassyAttestationFileCostingServices $embassyAttestationFileCostingServices
     */
    public function __construct(EmbassyAttestationFileCostingServices $embassyAttestationFileCostingServices)
    {
        $this->embassyAttestationFileCostingServices = $embassyAttestationFileCostingServices;
    }
    /**
     * Show the form for creating a new EmbassyAttestationFileCosting.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->embassyAttestationFileCostingServices->create($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Show the form for updating a EmbassyAttestationFileCosting.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->embassyAttestationFileCostingServices->update($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Remove the specified EmbassyAttestationFileCosting.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->embassyAttestationFileCostingServices->delete($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Deletion failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Retrieve the specified EmbassyAttestationFileCosting based on Country.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieveByCountry(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->embassyAttestationFileCostingServices->retrieveByCountry($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
