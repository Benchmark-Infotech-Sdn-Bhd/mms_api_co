<?php

namespace App\Imports;

use App\Models\CRMProspect;
use App\Services\CRMServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

class CrmImport implements ToModel, WithChunkReading, WithHeadingRow
{
    protected const CHUNK_ROW = 250;
    private $parameters;
    /**
     * @var string
     */
    private $bulkUpload;

    /**
     * @var CRMServices
     */
    private CRMServices $crmServices;

    /**
     * Create a new job instance.
     *
     * @param $parameters
     * @param string $bulkUpload
     * @param CRMServices $crmServices
     */
    public function __construct($parameters, $bulkUpload = '', CRMServices $crmServices)
    {
        $this->parameters = $parameters;
        $this->bulkUpload = $bulkUpload;
        $this->crmServices = $crmServices;
    }

    /**
     * @param array $row
     * @return Model|Model[]|void|null
     */
    public function model(array $row)
    {
        try {
                Log::info('Row Data' . print_r($row, true));

                $prospectService[0] = new \stdClass();
                $prospectService[0]->service_id = $row['service_type'];
                $prospectService[0]->service_name = Config::get('services.WORKER_MODULE_TYPE')[$row['service_type']-1];

                $loginCredential[0] = new \stdClass();
                $loginCredential[0]->system_id = 1;
                $loginCredential[0]->system_name = 'FWCMS';
                $loginCredential[0]->username = $row['fwcms_username'];
                $loginCredential[0]->password = $row['fwcms_password'];

                $loginCredential[1] = new \stdClass();
                $loginCredential[1]->system_id = 2;
                $loginCredential[1]->system_name = 'FOMEMA';
                $loginCredential[1]->username = $row['fomema_username'];
                $loginCredential[1]->password = $row['fomema_password'];

                $loginCredential[2] = new \stdClass();
                $loginCredential[2]->system_id = 3;
                $loginCredential[2]->system_name = 'Email Login Credentials';
                $loginCredential[2]->username = $row['email_login_username'];
                $loginCredential[2]->password = $row['email_login_password'];

                $loginCredential[3] = new \stdClass();
                $loginCredential[3]->system_id = 4;
                $loginCredential[3]->system_name = 'EPLKS';
                $loginCredential[3]->username = $row['eplks_username'];
                $loginCredential[3]->password = $row['eplks_password'];

                $loginCredential[4] = new \stdClass();
                $loginCredential[4]->system_id = 5;
                $loginCredential[4]->system_name = 'Myfuture Jobs';
                $loginCredential[4]->username = $row['myfuture_jobs_username'];
                $loginCredential[4]->password = $row['myfuture_jobs_password'];

                $loginCredential[5] = new \stdClass();
                $loginCredential[5]->system_id = 6;
                $loginCredential[5]->system_name = 'ESD';
                $loginCredential[5]->username = $row['esd_username'];
                $loginCredential[5]->password = $row['esd_password'];

                $loginCredential[6] = new \stdClass();
                $loginCredential[6]->system_id = 7;
                $loginCredential[6]->system_name = 'Pin Keselamatan';
                $loginCredential[6]->username = $row['pin_keselamatan_username'];
                $loginCredential[6]->password = $row['pin_keselamatan_password'];
                
                $crmParameter = new Request
                ([
                    'company_name' => $row['company_name'],
                    'contract_type' => $row['contract_type'],
                    'roc_number' => $row['roc_number'],
                    'director_or_owner' => $row['director_name'],
                    'contact_number' => $row['contact_number'],
                    'email' => $row['email'],                        
                    'address' => $row['address'],
                    'pic_name' => $row['pic_name'],
                    'pic_contact_number' => $row['pic_contact_number'],
                    'pic_designation' => $row['designation'],
                    'registered_by' => $row['registered_by'],
                    'sector_type' => $row['sector_type'],

                    'bank_account_name' => $row['bank_acc_name'],
                    'bank_account_number' => $row['bank_acc_number'],
                    'tax_id' => $row['tax_id_number'],

                    'prospect_service' => json_encode($prospectService),
                    'login_credential' => json_encode($loginCredential),

                    'created_by' => $row['created_by'] ?? 0,
                    'modified_by' => $row['modified_by'] ?? 0,
                    'company_id' => $row['fomnext_company_name']

                ]);
                Log::info('data - ' . print_r($crmParameter, true));
                $return = $this->crmServices->create($crmParameter);
                Log::info('data - ' . print_r($return, true));

        } catch (\Exception $exception) {
            Log::error('Error - ' . print_r($exception->getMessage(), true));
        }
    }

    /**
     * @param $date
     * @return string
     */
    public function dateConvert($date)
    {
        return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($date))->format('Y-m-d');
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return self::CHUNK_ROW;
    }
}
