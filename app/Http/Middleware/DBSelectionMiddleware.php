<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DBSelectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (DB::getDriverName() !== 'sqlite') {
            Config::set('cache.prefix', $request->identifier);
            $dbDetails = DB::connection('tenant')->table('domains')
                ->where('active_flag', 1)
                //->where('identifier', $request->identifier)
                ->first(['db_name', 'db_host', 'db_username', 'db_password']);
            if (!isset($dbDetails->db_name)) {
                return response()->json($this->frameResponse($this->sendResponse(['message' => 'Please enter a valid company url. Contact your manager if you have not received login credentials.'])), 400);
            }
            Config::set('database.default', 'mysql');
            Config::set('database.connections.mysql.host', $dbDetails->db_host);
            Config::set('database.connections.mysql.username', $dbDetails->db_username);
            Config::set('database.connections.mysql.password', $dbDetails->db_password);
            Config::set('database.connections.mysql.database', $dbDetails->db_name);
            DB::purge('mysql');
            DB::reconnect('mysql');
            Schema::connection('mysql')->getConnection()->reconnect();
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
