<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\URL;
use Exception;

class AccessControlMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $moduleId)
    {
        try {
            // $urll = URL::current();
            // print_r($urll);exit;
            // print_r($request);exit;
            echo($moduleId);exit;
            JWTAuth::parseToken()->authenticate();
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
