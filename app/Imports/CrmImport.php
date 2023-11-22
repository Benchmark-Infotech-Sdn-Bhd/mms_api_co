<?php

namespace App\Imports;

//use App\Jobs\WorkersImport;
use App\Models\CRMProspect;
use App\Services\CRMServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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
                
                $crmParameter = [
                    'company_name' => $this->parameters['company_name'],
                    'contract_type' => $this->parameters['contract_type'],
                    'roc_number' => $this->parameters['roc_number'],
                    'director_or_owner' => $row['director_name'],
                    'contact_number' => $row['contact_number'],
                    'email' => $row['email'],                        
                    'address' => $row['address'],
                    'pic_name' => $row['pic_name'],
                    'pic_contact_number' => $row['pic_contact_number'],
                    'pic_designation' => $row['designation'],
                    'registered_by' => $row['registered_by'],
                    'sector_type' => $row['sector_type'],

                    'prospect_service' => $row['prospect_service'],
                    'login_credential' => $row['login_credential'],

                    'created_by' => $row['created_by'] ?? 0,
                    'modified_by' => $row['modified_by'] ?? 0,
                    'company_id' => $row['fomnext company_name']
                ];
                //echo "<pre>"; print_r($crmParameter); exit;
                
                $this->crmServices->create($crmParameter);

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
