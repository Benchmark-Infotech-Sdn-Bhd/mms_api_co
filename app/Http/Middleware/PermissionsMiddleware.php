<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class PermissionsMiddleware
{
    /**
     * Handle the request, perform necessary actions based on user type and permissions.
     *
     * @param $request
     * @param Closure $next
     * @param string $module
     * @param string $permissionName
     * @return mixed
     */
    public function handle($request, Closure $next, $module, $permissionName)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user['user_type'] != 'Admin') {
                if ($user['user_type'] === 'Super Admin') {
                    return $this->handleSuperAdmin($module, $request);
                }
                return $this->handleUser($user, $module, $permissionName, $request);
            }
        } catch (Exception $e) {
            Log::error('Exception - ' . print_r($e->getMessage(), true));
            return response()->json($this->frameResponse(['message' => 'Authorization Token not found']));
        }
        return $next($request);
    }

    /**
     * Handles the super admin role for a given module and request.
     *
     * @param int $module The module ID.
     * @param Request $request The request object.
     * @return JsonResponse|Request The response as a JSON object or an empty array.
     */
    private function handleSuperAdmin($module, $request)
    {
        if (in_array($module, [14, 15])) {
            return $request;
        }
        return response()->json($this->emptyFrameResponse());
    }

    /**
     * Handles user authorization for a specific action
     *
     * @param mixed $user The user object or identifier
     * @param string $module The module name
     * @param string $permissionName The name of the permission
     * @param mixed $request The request object or data
     * @return JsonResponse The response object or data, or the original request data
     */
    private function handleUser($user, $module, $permissionName, $request)
    {
        $userRole = $this->getUserRole($user);

        if (is_null($userRole)) {
            return response()->json($this->frameResponse($this->sendResponse(['message' => 'Unauthorized to perform this action!'])), 400);
        }

        $permissionId = $this->getPermissionId($permissionName);
        $rolePermission = $this->getRolePermission($userRole, $module, $permissionId);


        if ($rolePermission == 0) {
            return response()->json($this->frameResponse($this->sendResponse(['message' => 'Unauthorized to perform this action!'])), 400);
        }
        return $request;
    }

    /**
     * Retrieves the role of a user.
     *
     * @param array $user The user data.
     * @return Model|Builder|object|null The role object or null if the user has no role.
     */
    private function getUserRole($user)
    {
        return DB::table('user_role_type')->join('roles', function ($join) {
            $join->on('roles.id', '=', 'user_role_type.role_id')
                ->where('roles.status', '=', 1);
        })->where('user_id', $user['id'])->first();
    }

    /**
     * Retrieves the ID of a permission by its name.
     *
     * @param string $permissionName The name of the permission.
     * @return Model|Builder|object|null The permission ID or null if the permission does not exist.
     */
    private function getPermissionId($permissionName)
    {
        return DB::table('permissions')->where('permission_name', $permissionName)->first('id');
    }

    /**
     * Retrieves the number of role permissions for a given user role, module, and permission id.
     *
     * @param Model|Builder|object|null $userRole The role object retrieved from the getUserRole method.
     * @param int $module The module id for which the permissions are being checked.
     * @param $permissionId - The permission id which is being checked.
     * @return int The number of role permissions matching the specified criteria.
     */
    private function getRolePermission($userRole, $module, $permissionId)
    {
        return DB::table('role_permission')->where('role_id', $userRole->role_id)
            ->where('module_id', $module)
            ->where(function ($query) use ($permissionId) {
                $query->where('permission_id', '=', $permissionId->id)
                    ->orWhere('permission_id', '=', 1);
            })->count();
    }

    /**
     * Frames the response data.
     *
     * @param mixed $data The data to be included in the response.
     * @return array The framed response array.
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
     * Sends a response.
     *
     * @param mixed $response The response data.
     * @return array|object The encrypted response or the response object, depending on the encryption settings.
     */
    protected function sendResponse($response)
    {
        if (config('security.encrypt_enabled') === true) {
            return ($response) ? app('securityServices')->encrypt(json_encode($response)) : [];
        } else {
            return (object)$response;
        }
    }

    /**
     * Returns an empty frame response.
     *
     * This method returns an array containing an empty frame response with default values.
     *
     * @return array Returns an array with the following keys:
     *  - 'error' (bool): Indicates if an error occurred. Default value is false.
     *  - 'statusCode' (int): The status code of the response. Default value is 200.
     *  - 'statusMessage' (string): The status message of the response. Default value is 'OK'.
     *  - 'data' (string): The data of the response. Default value is an empty string.
     *  - 'responseTime' (int): The timestamp of the response. Default value is the current time.
     */
    protected function emptyFrameResponse(): array
    {
        return [
            'error' => false,
            'statusCode' => 200,
            'statusMessage' => 'OK',
            'data' => '',
            'responseTime' => time()
        ];
    }
}
