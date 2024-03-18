<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class DBSelectionMiddleware
{
    const MYSQL_CONNECTION_TYPE = 'mysql';
    const TENANT_CONNECTION_TYPE = 'tenant';
    const UNAUTHORIZED_MESSAGE = 'Please enter a valid company url. Contact your manager if you have not received login credentials.';
    const AUTH_ERROR_STATUS_CODE = 400;

    /**
     * Handle the request.
     *
     * @param Request $request The incoming request.
     * @param Closure $next The next middleware closure.
     * @return mixed The response returned by the next middleware closure.
     */
    public function handle($request, Closure $next)
    {
        if (DB::getDriverName() !== 'sqlite') {
            Config::set('cache.prefix', $request->domain_name);
            $dbDetails = $this->getDatabaseDetails($request->domain_name);
            if (!isset($dbDetails->db_name)) {
                return $this->generateUnauthorizedResponse();
            }
            $this->establishMySQLConnection($dbDetails);
        }
        return $next($request);
    }

    /**
     * Retrieves the database details for a given domain.
     *
     * @param string $domainName The domain identifier.
     * @return stdClass|null Returns an object containing the database details if found,
     *                      or null if no matching domain is found.
     */
    protected function getDatabaseDetails($domainName)
    {
        return DB::connection(self::TENANT_CONNECTION_TYPE)->table('domains')
            ->where('active_flag', 1)
            ->where('identifier', $domainName)
            ->first(['db_name', 'db_host', 'db_username', 'db_password']);
    }

    /**
     * Establishes a MySQL database connection using the provided database details.
     *
     * @param stdClass $dbDetails An object containing the database details.
     *                           The object should have the following properties:
     *                           - db_host: The MySQL host name or IP address.
     *                           - db_username: The MySQL username.
     *                           - db_password: The MySQL password.
     *                           - db_name: The name of the MySQL database.
     * @return void
     */
    protected function establishMySQLConnection($dbDetails)
    {
        Config::set('database.default', self::MYSQL_CONNECTION_TYPE);
        Config::set('database.connections.' . self::MYSQL_CONNECTION_TYPE . '.host', $dbDetails->db_host);
        Config::set('database.connections.' . self::MYSQL_CONNECTION_TYPE . '.username', $dbDetails->db_username);
        Config::set('database.connections.' . self::MYSQL_CONNECTION_TYPE . '.password', $dbDetails->db_password);
        Config::set('database.connections.' . self::MYSQL_CONNECTION_TYPE . '.database', $dbDetails->db_name);
        DB::purge(self::MYSQL_CONNECTION_TYPE);
        DB::reconnect(self::MYSQL_CONNECTION_TYPE);
        Schema::connection(self::MYSQL_CONNECTION_TYPE)->getConnection()->reconnect();
    }

    /**
     * Generates an unauthorized response.
     *
     * This method generates an unauthorized response by creating a JSON response using the sendResponse() method
     * with a specified message indicating unauthorized access, and wrapping it in a response() function call
     * along with the appropriate error status code.
     *
     * @return JsonResponse The generated unauthorized response.
     */
    protected function generateUnauthorizedResponse()
    {
        return response()->json($this->frameResponse($this->sendResponse(['message' => self::UNAUTHORIZED_MESSAGE])), self::AUTH_ERROR_STATUS_CODE);
    }

    /**
     * Frames a response by adding necessary attributes and values to the given data.
     *
     * This method takes the provided data and adds attributes such as 'status', 'statusCode', 'statusMessage',
     * 'responseTime', and wraps it in an array before returning it.
     *
     * @param mixed $data The data to be framed in the response.
     * @return array The framed response with added attributes.
     */
    protected function frameResponse($data): array
    {
        return [
            'status' => false,
            'statusCode' => self::AUTH_ERROR_STATUS_CODE,
            'statusMessage' => 'Bad Request',
            'data' => $data,
            'responseTime' => time()
        ];
    }

    /**
     * Sends a response.
     *
     * This method sends a response by either encrypting the provided response using the SecurityServices class
     * and returning the encrypted response, or by simply returning the response as an object, depending on
     * the value of the `encrypt_enabled` configuration option.
     *
     * @param mixed $response The response to be sent.
     * @return mixed The sent response, either encrypted or as an object.
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
