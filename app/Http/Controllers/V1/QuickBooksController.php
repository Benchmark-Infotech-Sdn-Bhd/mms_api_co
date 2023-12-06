<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\QuickBooksServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class QuickBooksController extends Controller
{
    /**
     * @var QuickBooksServices
     */
    private QuickBooksServices $quickBooksServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * AgentController constructor.
     * @param QuickBooksServices $quickBooksServices
     * @param AuthServices $authServices
     */
    public function __construct(QuickBooksServices $quickBooksServices, AuthServices $authServices)
    {
        $this->quickBooksServices = $quickBooksServices;
        $this->authServices = $authServices;
    }

    /**
     * Show the form for listing Quick books accessToken.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function accessToken(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->quickBooksServices->accessToken($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Listing failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Show the form for listing Quick books refreshToken.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->quickBooksServices->refreshToken($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Show the form for listing Quick books invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function invoiceShow(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);   
            $data = $this->quickBooksServices->invoiceShow($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Show the form for listing Quick books invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function invoiceCreate(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->quickBooksServices->invoiceCreate($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }


    /**
     * Show the form for listing Quick books invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function customerCreateError(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->quickBooksServices->customerCreateError($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

     /**
     * Show the form for listing Quick books invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function customerCreate(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->quickBooksServices->customerCreate($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Show the form for listing Quick books invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function customerShow(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->quickBooksServices->customerShow($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Unable to get the data. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    
}
