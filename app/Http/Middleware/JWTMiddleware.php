<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Exception;

class JWTMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json($this->frameResponse(['message' => 'Token is Invalid']));
            } else if ($e instanceof TokenExpiredException) {
                return response()->json($this->frameResponse(['message' => 'Token is Expired']));
            } else {
                return response()->json($this->frameResponse(['message' => 'Authorization Token not found']));
            }
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
