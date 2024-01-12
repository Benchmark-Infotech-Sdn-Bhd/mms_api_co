<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DatabaseConnectionServices
{
    /**
     * Class constructor.
     *
     * Initializes a new instance of the class.
     *
     * @param mixed $parameters The parameters for the constructor.
     * @param string $bulkUpload (Optional) The bulk upload value. Default is an empty string.
     *
     * @return void
     */
    public function __construct()
    {
    }
    /**
     * Attempt to connect the respective Database based on the DB Name passed from the Queue
     *
     * @param $request Input of the current DB from the Queue Dispatch request
     * 
     * @return void
     */
    public function dbConnectQueue($request): void
    {
        //if (DB::getDriverName() !== 'sqlite') {
            $dbDetails = DB::connection('tenant')->table('domains')
                ->where('active_flag', 1)
                ->where('db_name', $request)
                ->first(['db_name', 'db_host', 'db_username', 'db_password', 'identifier']);
            Config::set('cache.prefix', $dbDetails->identifier);

            DB::purge('mysql');
            try
            {
                
                Config::set('database.default', 'mysql');
                Config::set('database.connections.mysql.host', $dbDetails->db_host);
                Config::set('database.connections.mysql.username', $dbDetails->db_username);
                Config::set('database.connections.mysql.password', $dbDetails->db_password);
                Config::set('database.connections.mysql.database', $dbDetails->db_name);
            
                DB::reconnect('mysql');
                Schema::connection('mysql')->getConnection()->reconnect();

            }catch (\Exception $e)
            {
                throw new \Exception($e);
            }
        //}
    }
}