<?php

namespace App\Jobs;

use App\Models\Workers;
use App\Models\WorkerKin;
use App\Models\WorkerVisa;
use App\Models\WorkerBioMedical;
use App\Models\BulkUploadRecords;
use App\Models\WorkerFomema;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerBankDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class CommonWorkersImport extends Job
{
    private $bulkUpload;
    private $workerParameter;
    private $workerNonMandatory;

    /**
     * Create a new job instance.
     *
     * @param $workerParameter
     * @param $bulkUpload
     * @param $workerNonMandatory
     */
    public function __construct($workerParameter, $bulkUpload, $workerNonMandatory)
    {
        $this->workerParameter = $workerParameter;
        $this->bulkUpload = $bulkUpload;
        $this->workerNonMandatory = $workerNonMandatory;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('Worker instert - started ');

        $comments = '';
        $validationCheck = $this->createValidation($this->workerParameter);
        if(empty($validationCheck)) {

            $workerRelationship = DB::table('kin_relationship')
                                    ->where('name', $this->workerParameter['kin_relationship'])
                                    ->first('id');

            if(is_null($workerRelationship)) {
                $comments .= ' ERROR - Invalid Kin Relationship';
                DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
                Log::info('ERROR - worker import failed  due to '.$comments);
            } else {
                Log::info('Row Data - realtionship - ' . print_r($workerRelationship->id, true));
                $workerCount = DB::table('workers')
                                    ->where('passport_number', $this->workerParameter['passport_number'])
                                    ->count();
                if($workerCount > 0) {
                    $comments .= 'ERROR - worker - passport already exist '.$this->workerParameter['passport_number'];
                    DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
                    Log::info('ERROR - worker import failed  due to '.$comments);
                } else {
                    $worker = Workers::create([            
                        'name' => $this->workerParameter['name'] ?? '',
                        'gender' => $this->workerParameter['gender'] ?? '',
                        'date_of_birth' => $this->workerParameter['date_of_birth'] ?? '',
                        'passport_number' => $this->workerParameter['passport_number'] ?? '',
                        'passport_valid_until' => $this->workerParameter['passport_valid_until'] ?? '',
                        'fomema_valid_until' => null,
                        'status' => 1,
                        'address' => $this->workerParameter['address'] ?? '',
                        'city' => $this->workerParameter['city'] ?? '',
                        'state' => $this->workerParameter['state'] ?? '',
                        'crm_prospect_id' => $this->workerNonMandatory['crm_prospect_id'] ?? 0,
                        'created_by'    => $this->workerParameter['created_by'] ?? 0,
                        'modified_by'   => $this->workerParameter['created_by'] ?? 0,
                        'company_id'    => $this->workerParameter['company_id'] ?? 0,
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString()
                    ]);
                    WorkerKin::create([
                        'worker_id' => $worker['id'],
                        'kin_name' => $this->workerParameter['kin_name'] ?? '',
                        'kin_relationship_id' => $workerRelationship->id ?? 0,
                        'kin_contact_number' =>  $this->workerParameter['kin_contact_number'] ?? '',
                        'created_by'    => $this->workerParameter['created_by'] ?? 0,
                        'modified_by'   => $this->workerParameter['created_by'] ?? 0,
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString()         
                    ]);
                    WorkerVisa::create([
                        'worker_id' => $worker['id'],
                        'ksm_reference_number' => $this->workerParameter['ksm_reference_number'],
                        'work_permit_valid_until' =>  $this->workerParameter['work_permit_valid_until'] ?? '',
                        'created_by'    => $this->workerParameter['created_by'] ?? 0,
                        'modified_by'   => $this->workerParameter['created_by'] ?? 0,
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString() 
                    ]);
                    WorkerBioMedical::create([
                        'worker_id' => $worker['id'],
                        'bio_medical_reference_number' => $this->workerParameter['bio_medical_reference_number'],
                        'bio_medical_valid_until' => $this->workerParameter['bio_medical_valid_until'],
                        'created_by'    => $this->workerParameter['created_by'] ?? 0,
                        'modified_by'   => $this->workerParameter['created_by'] ?? 0,
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString() 
                    ]);
                    WorkerFomema::create([
                        'worker_id' => $worker['id'],
                        'purchase_date' => $this->workerParameter['purchase_date'] ?? '',
                        'clinic_name' => $this->workerParameter['clinic_name'] ?? '',
                        'doctor_code' =>  $this->workerParameter['doctor_code'] ?? '',         
                        'allocated_xray' =>  $this->workerParameter['allocated_xray'] ?? '',
                        'xray_code' =>  $this->workerParameter['xray_code'] ?? '',
                        'created_by'    => $this->workerParameter['created_by'] ?? 0,
                        'modified_by'   => $this->workerParameter['created_by'] ?? 0,
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString()
                    ]);
                    WorkerInsuranceDetails::create([
                        'worker_id' => $worker['id'],
                        'ig_policy_number' => $this->workerParameter['ig_policy_number'],
                        'hospitalization_policy_number' => $this->workerParameter['hospitalization_policy_number'],         
                        'insurance_expiry_date' =>  $this->workerParameter['insurance_expiry_date'] ?? '',
                        'created_by'    => $this->workerParameter['created_by'] ?? 0,
                        'modified_by'   => $this->workerParameter['created_by'] ?? 0,
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString()
                    ]);
                    WorkerBankDetails::create([
                        'worker_id' => $worker['id'],
                        'bank_name' => $this->workerNonMandatory['bank_name'] ?? '',
                        'account_number' => $this->workerNonMandatory['account_number'] ?? '',
                        'socso_number' =>  $this->workerNonMandatory['socso_number'] ?? '',
                        'created_by'    => $this->workerParameter['created_by'] ?? 0,
                        'modified_by'   => $this->workerParameter['created_by'] ?? 0,
                        'created_at'    => Carbon::now()->toDateTimeString(),
                        'updated_at'    => Carbon::now()->toDateTimeString()
                    ]);

                    Log::info('Worker inserted -  '.$worker['id']);
                    DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_success');
                    $comments .= ' SUCCESS - worker imported';
                }
            }
        } else {
            DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
            Log::info('ERROR - required params are empty');
            $comments .= ' ERROR - required params are empty'. join($validationCheck);
        }
        $this->insertRecord($comments);
    }
    /**
     * @param $workers
     * @return array
     */
    public function createValidation($workers): array
    {
        $emptyFields = [];
        $i=0;
        foreach($workers as $key => $worker) {
            // Log::info($key);
            if(empty($worker)) {
                $emptyFields[$i++] = $key . " is required";
            }
        }
        return $emptyFields;
    }
    /**
     * @param string $comments
     * @param int $status
     */
    public function insertRecord($comments = '', $status = 1): void
    {
        BulkUploadRecords::create(
            [
                'bulk_upload_id' => $this->bulkUpload->id,
                'parameter' => json_encode($this->workerParameter),
                'comments' => $comments,
                'status' => $status
            ]
        );
    }
}
