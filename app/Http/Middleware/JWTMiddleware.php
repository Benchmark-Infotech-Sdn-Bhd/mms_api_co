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
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (TokenInvalidException $e) {
            Log::error('TokenExpiredException - ' . print_r($e->getMessage(), true));
            return response()->json($this->frameResponse(['message' => 'Token is Invalid']));
        } catch (TokenExpiredException $e) {
            Log::error('TokenExpiredException - ' . print_r($e->getMessage(), true));
            return response()->json($this->frameResponse(['message' => 'Token is Expired']));
        } catch (Exception $e) {
            Log::error('Exception - ' . print_r($e->getMessage(), true));
            return response()->json($this->frameResponse(['message' => 'Authorization Token not found']));
        }
        return $next($request);
    }

    /**
     * @param array|object $data
     * @return array
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
