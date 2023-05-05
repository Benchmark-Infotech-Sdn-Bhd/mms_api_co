<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DocumentChecklistAttachmentsServices;
use Illuminate\Support\Facades\Log;

class DocumentChecklistAttachmentsController extends Controller
{
    /**
     * @var DocumentChecklistAttachmentsServices
     */
    private DocumentChecklistAttachmentsServices $documentChecklistAttachmentsServices;

    /**
     * DocumentChecklistAttachmentsController constructor.
     * @param DocumentChecklistAttachmentsServices $documentChecklistAttachmentsServices
     */
    public function __construct(DocumentChecklistAttachmentsServices $documentChecklistAttachmentsServices)
    {
        $this->documentChecklistAttachmentsServices = $documentChecklistAttachmentsServices;
    }
    /**
     * Show the form for creating a new DocumentChecklistAttachment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $this->documentChecklistAttachmentsServices->create($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Remove the specified DocumentChecklist Attachment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->documentChecklistAttachmentsServices->delete($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Deletion failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    /**
     * Retrieve all DocumentChecklist with Attachments based on Sector & Application.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->documentChecklistAttachmentsServices->list($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
}
