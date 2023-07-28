<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected array $errorMessage = [
        200 => 'OK',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        412 => 'Precondition Failed',
        415 => 'Unsupported Media Type',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
        501 => 'Not Implemented'
    ];

    /**
     * @param $token
     * @param array $user
     * @return JsonResponse
     */
    protected function respondWithToken($token, $user=[])
    {
        return $this->sendSuccess([
            'token' => $token,
            'user' => $user,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function getRequest($request)
    {
        return $request->all();
    }

    /**
     * @param $response
     * @return JsonResponse
     */
    protected function validationError($response): JsonResponse
    {
        return response()->json(
            $this->frameResponse(true, 422, 'Unprocessable Entity Error', $this->sendResponse($response)), 422);
    }

    /**
     * @param bool $status
     * @param int $statusCode
     * @param string $statusMessage
     * @param array|object $data
     * @return array
     */
    protected function frameResponse(bool $status, int $statusCode, string $statusMessage, $data): array
    {
        return [
            'error' => $status,
            'statusCode' => $statusCode,
            'statusMessage' => $statusMessage,
            'data' => $data,
            'responseTime' => time()
        ];
    }

    /**
     * @param $response
     * @return mixed
     */
    protected function sendResponse($response)
    {
        return $response;
    }

    /**
     * @param $response
     * @param int $status
     * @return JsonResponse
     */
    protected function sendError($response, int $status = 400): JsonResponse
    {
        return response()->json(
            $this->frameResponse(true, $status, $this->errorMessage[$status], $this->sendResponse($response)));
    }

    /**
     * @param $response
     * @return JsonResponse
     */
    protected function sendSuccess($response): JsonResponse
    {
        return response()->json(
            $this->frameResponse(false, 200, 'OK', $this->sendResponse($response)));
    }

}
