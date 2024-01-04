<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Services\AuthServices;

class PermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * 
     */
    public function handle($request, Closure $next, $module, $permissionName)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if($user['user_type'] != 'Admin'){

                if($user['user_type'] == 'Super Admin') {
                    if(in_array($module, [14,15])) {
                        return $next($request);
                    } else {
                    return response()->json($this->emptyFrameResponse());
                    }
                } else {

                    $userRole = DB::table('user_role_type')->join('roles',function($join){
                        $join->on('roles.id','=','user_role_type.role_id');
                        $join->where('roles.status','=',1);
                    })
                    ->where('user_id',$user['id'])->first();

                    if(is_null($userRole)){
                        return response()->json($this->frameResponse($this->sendResponse(['message' => 'Unauthorized to perform this action!'])), 400);
                    }

                    //$module = DB::table('modules')->where('module_name', $module)->first();

                    $permissionId = DB::table('permissions')->where('permission_name', $permissionName)->first('id');

                    $rolePermission = DB::table('role_permission')
                    ->where('role_id', $userRole->role_id)
                    ->where('module_id', $module)
                    ->where(function ($query) use ($permissionId) {
                        $query->where('permission_id', '=', $permissionId->id)
                            ->orWhere('permission_id', '=', 1);
                    })
                    ->count();

                    if ($rolePermission == 0) {
                        return response()->json($this->frameResponse($this->sendResponse(['message' => 'Unauthorized to perform this action!'])), 400);
                    }
                }

            }
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
            'status' => false,
            'statusCode' => 400,
            'statusMessage' => 'Bad Request',
            'data' => $data,
            'responseTime' => time()
        ];
    }

    /**
     * @param $response
     * @return array|object
     */
    protected function sendResponse($response)
    {
        if (config('security.encrypt_enabled') === true) {
            return ($response) ? app('securityServices')->encrypt(json_encode($response)) : [];
        } else {
            return (object)$response;
        }
    }
}