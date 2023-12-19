<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\CompanyModulePermission;
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
            $user = JWTAuth::parseToken()->authenticate();
            $checkModule = CompanyModulePermission::where([
                'company_id' => $user['company_id'],
                'module_id' => $moduleId
            ])->count('id');
            if($checkModule == 0) {
                return response()->json($this->frameResponse(['message' => 'Unauthorized']));
            }
        } catch (Exception $e) {
            Log::error('Exception - ' . print_r($e->getMessage(), true));
            return response()->json($this->frameResponse(['message' => 'Unauthorized']));
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
