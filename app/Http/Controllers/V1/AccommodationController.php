<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AccommodationServices;
use Exception;
use Illuminate\Support\Facades\Log;

class AccommodationController extends Controller
{
    /**
     * @var accommodationServices
     */
    private AccommodationServices $accommodationServices;

    /**
     * Class constructor.
     *
     * @param AccommodationServices $accommodationServices The accommodation services object.
     *
     * @return void
     */
    public function __construct(AccommodationServices $accommodationServices)
    {
        $this->accommodationServices = $accommodationServices;
    }

    /**
     * Create a new accommodation.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response object.
     */
    public function create(Request $request): JsonResponse
    {
        try {

            $validation = $this->accommodationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->accommodationServices->create($request);
            if (isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => $response['unauthorizedError']]);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Accommodation creation was failed']);
        }
    }

    /**
     * Lists the accommodations based on the request parameters.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response with the list of accommodations.
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->accommodationServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve all accommodation data was failed']);
        }
    }

    /**
     * Show method.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response with success or error message.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->accommodationServices->show($params['id']);
            if (is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve accommodation data was failed']);
        }
    }

    /**
     * Update the accommodation.
     *
     * @param Request $request The request object containing the accommodation data.
     *
     * @return JsonResponse The JSON response containing the result of the update operation.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validation = $this->accommodationServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->accommodationServices->update($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Accommodation update was failed']);
        }
    }

    /**
     * Deletes accommodation.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $response = $this->accommodationServices->delete($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete accommodation was failed']);
        }
    }

    /**
     * Delete attachment.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response.
     */
    public function deleteAttachment(Request $request): JsonResponse
    {
        try {
            $response = $this->accommodationServices->deleteAttachment($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete attachments was failed']);
        }
    }

}
