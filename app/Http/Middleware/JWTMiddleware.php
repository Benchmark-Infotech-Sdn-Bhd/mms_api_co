<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Exception;

class JWTMiddleware
{
    /**
     * Handle the incoming request and authenticate the token.
     *
     * @param Request $request The incoming request object.
     * @param Closure $next The closure representing the next middleware or endpoint.
     *
     * @return mixed The result of the next middleware or endpoint.
     *
     * @throws TokenInvalidException If the token is invalid.
     * @throws TokenExpiredException If the token is expired.
     * @throws Exception             If an exception occurs during authentication.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (TokenInvalidException $e) {
            Log::info('TokenExpiredException - ' . print_r($e->getMessage(), true));
            return response()->json($this->frameResponse(['message' => 'Token is Invalid']));
        } catch (TokenExpiredException $e) {
            Log::info('TokenExpiredException - ' . print_r($e->getMessage(), true));
            return response()->json($this->frameResponse(['message' => 'Token is Expired']));
        } catch (Exception $e) {
            Log::info('Exception - ' . print_r($e->getMessage(), true));
            return response()->json($this->frameResponse(['message' => 'Authorization Token not found']));
        }
        return $next($request);
    }

    /**
     * Frame the response with error details, status code, status message, data, and response time.
     *
     * @param mixed $data The data to be included in the response.
     *
     * @return array The framed response as an associative array with the following keys:
     *               - 'error'         : A boolean indicating if an error occurred.
     *               - 'statusCode'    : The HTTP status code of the response.
     *               - 'statusMessage' : The status message of the response.
     *               - 'data'          : The data included in the response.
     *               - 'responseTime'  : The timestamp indicating the response time.
     */
    protected function frameResponse($data): array
    {
        return [
            'error' => true,
            'statusCode' => 400,
            'statusMessage' => 'Bad Request',
            'data' => (object)$data,
            'responseTime' => time()
        ];
    }
}
