<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceCallbackLogsServices
{
    private $insertData;

    /**
     * Assign the basic information for API log
     * @param $url
     * @param array $params
     */
    public function startApiLog($url, $params = [])
    {
        $apiLogParams['url'] = $url;
        //$apiLogParams['parameter'] = json_encode($params);
        $apiLogParams['response'] = json_encode($params);
        $apiLogParams['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $apiLogParams['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $this->insertData = DB::table('invoice_callback_logs')->insertGetId($apiLogParams);

    }

    /**
     * store the API log details into DB
     * @param $response
     */
    public function endApiLog($response)
    {
        DB::table('invoice_callback_log')->where('id', $this->insertData)
            ->update([
                'response' => json_encode($response),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
    }
}
