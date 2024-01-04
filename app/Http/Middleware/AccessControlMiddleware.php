<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\CompanyModulePermission;
use Exception;

class AccessControlMiddleware
{
    /**
     * Handle the request and perform necessary authorization checks.
     *
     * @param Request $request
     * @param Closure $next
     * @param int $moduleId
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $moduleId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($this->isSuperAdminUser($user) && $this->hasModuleAccess($moduleId)) {
                return $next($request);
            }

            if ($this->hasCompanyModulePermission($user, $moduleId)) {
                return $next($request);
            }

            return $this->accessDeniedResponse();

        } catch (Exception $e) {
            Log::error('Exception - ' . print_r($e->getMessage(), true));

            return $this->accessDeniedResponse();
        }
    }

    /**
     * Checks if the user is a super admin.
     *
     * @param array $user The user data.
     * @return bool Returns true if the user is a super admin, false otherwise.
     */
    private function isSuperAdminUser($user): bool
    {
        return $user['user_type'] == 'Super Admin';
    }

    /**
     * Checks if a user has access to a specific module.
     *
     * @param int $moduleId The ID of the module to check access for.
     *
     * @return bool Returns true if the user has access to the specified module, otherwise false.
     */
    private function hasModuleAccess($moduleId): bool
    {
        return in_array($moduleId, [14, 15]);
    }

    /**
     * Checks if a user has permission to access a specific module for their company.
     *
     * @param array $user The user information.
     * @param int $moduleId The ID of the module to check permission for.
     *
     * @return bool Returns true if the user has permission to access the specified module, otherwise false.
     */
    private function hasCompanyModulePermission($user, $moduleId): bool
    {
        $checkModule = CompanyModulePermission::where([
            'company_id' => $user['company_id'],
            'module_id' => $moduleId
        ])->count('id');

        return $checkModule > 0;
    }

    /**
     * Generates an access denied response.
     *
     * This method generates a JSON response indicating that access has been denied.
     *
     * @return JsonResponse The generated access denied response.
     */
    private function accessDeniedResponse()
    {
        return response()->json($this->frameResponse(['message' => 'Access Denied!']));
    }

    /**
     * Frames the response with the specified data.
     *
     * @param mixed $data The data to be included in the response.
     *
     * @return array Returns an array containing the framed response with the following keys:
     * - 'error' (bool): Indicates whether an error occurred or not.
     * - 'statusCode' (int): The HTTP status code associated with the response.
     * - 'statusMessage' (string): The status message associated with the response.
     * - 'data' (object): The data object included in the response.
     * - 'responseTime' (int): The timestamp indicating the response time.
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
