<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ThirdPartyLogServices
{
    private $insertData;

    /**
     * Assign the basic information for API log
     * @param $url
     * @param array $params
     * 
     * @return void
     */
    public function startApiLog($url, $params = [])
    {
        $apiLogParams['url'] = $url;
        $apiLogParams['parameter'] = json_encode($params);
        $apiLogParams['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $apiLogParams['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $this->insertData = DB::table('third_party_api_logs')->insertGetId($apiLogParams);
 
    }

    /**
     * store the API log details into DB
     * @param $response
     * 
     * @return void
     * 
     */
    public function endApiLog($response)
    {
        DB::table('third_party_api_logs')->where('id', $this->insertData)
            ->update([
                'response' => json_encode($response),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
    }

    /**
     * Delete the log
     * 
     * @return mixed
     * 
     */
    public function delete() : mixed
    {
        try {
            $conditionDate = Carbon::now()->subDays(Config::get('services.THIRDPARTYLOG_DELETE_DURATION'))->toDateTimeString();
            $data = DB::table('third_party_api_logs')->where('created_at', '<=', $conditionDate)->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Exception in delete' . $e);
            return false;
        }
    }
}
