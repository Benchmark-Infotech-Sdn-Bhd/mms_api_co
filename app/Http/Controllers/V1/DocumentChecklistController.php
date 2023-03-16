<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DocumentChecklistServices;
use Illuminate\Support\Facades\Log;

class DocumentChecklistController extends Controller
{
    /**
     * @var DocumentChecklistServices
     */
    private DocumentChecklistServices $documentChecklistServices;

    /**
     * DocumentChecklistController constructor.
     * @param DocumentChecklistServices $documentChecklistServices
     */
    public function __construct(DocumentChecklistServices $documentChecklistServices)
    {
        $this->documentChecklistServices = $documentChecklistServices;
    }
    /**
     * Show the form for creating a new DocumentChecklist.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->documentChecklistServices->create($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Show the form for updating a DocumentChecklist.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->documentChecklistServices->update($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Remove the specified DocumentChecklist.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->documentChecklistServices->delete($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Deletion failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Retrieve the specified DocumentChecklist based on Sectors.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieveBySector(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->documentChecklistServices->retrieveBySector($params);
            return response()->json(['result' => $this->sendSuccess($data)]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
