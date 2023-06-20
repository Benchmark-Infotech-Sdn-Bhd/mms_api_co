<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerImmigration;
use App\Models\WorkerImmigrationAttachments;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DirectRecruitmentImmigrationFeePaidServices
{
    /**
     * @var directRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var Workers
     */
    private Workers $workers;

    /**
     * @var WorkerImmigration
     */
    private WorkerImmigration $workerImmigration;

    /**
     * @var WorkerImmigrationAttachments
     */
    private WorkerImmigrationAttachments $workerImmigrationAttachments;

    /**
     * @var Storage
     */
    private Storage $storage;
    

    /**
     * DirectRecruitmentImmigrationFeePaidServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param Workers $workers
     * @param WorkerImmigration $workerImmigration
     * @param WorkerImmigrationAttachments $workerImmigrationAttachments
     * @param Storage $storage;
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, Workers $workers, WorkerImmigration $workerImmigration, WorkerImmigrationAttachments $workerImmigrationAttachments, Storage $storage)
    {
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->workers                            = $workers;
        $this->workerImmigration                  = $workerImmigration;
        $this->workerImmigrationAttachments       = $workerImmigrationAttachments;
        $this->storage                            = $storage;
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'onboarding_country_id' => 'required',
                'agent_id' => 'required',
                'total_fee' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
                'immigration_reference_number' => 'required',
                'payment_date' => 'required|date|date_format:Y-m-d'
            ];
    }
    /**
     * @return array
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {

                if (request()->hasFile('attachment')){
                    foreach($request->file('attachment') as $file){
                        $fileName = $file->getClientOriginalName();
                        $filePath = '/directRecruitment/onboarding/immigrationFeePaid/' . $fileName; 
                        $linode = $this->storage::disk('linode');
                        $linode->put($filePath, file_get_contents($file));
                        $fileUrl = $this->storage::disk('linode')->url($filePath);  
                    }
                }else{
                    $fileName = '';
                    $fileUrl = '';
                }

                foreach ($request['workers'] as $workerId) {

                    $this->workerImmigration->updateOrCreate(
                        ['worker_id' => $workerId],
                        ['total_fee' => $request['total_fee'], 
                        'immigration_reference_number' => $request['immigration_reference_number'], 
                        'payment_date' => $request['payment_date'],
                        'immigration_status' => 'Paid', 
                        'created_by' => $params['created_by'],
                        'modified_by' => $params['created_by']
                    ]);

                    if(!empty($fileName) && !empty($fileUrl)){
                        $this->workerImmigrationAttachments->updateOrCreate(
                            ['file_id' => $workerId],
                            ["file_name" => $fileName,
                            "file_type" => 'Immigration Fee Paid',
                            "file_url" =>  $fileUrl]);
                    }

                }
                $this->directRecruitmentCallingVisaStatus->where([
                    'application_id' => $request['application_id'],
                    'onboarding_country_id' => $request['onboarding_country_id'],
                    'agent_id' => $request['agent_id']
                ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['created_by']]);
                return true;
            
        }else{
            return false;
        }
        
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workersList($request): mixed
    {
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'workers.id')
            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
            ->where('worker_visa.ksm_reference_number', $request['ksm_reference_number'])
            ->where('workers.cancel_status', 0)
            ->select('workers.id', 'workers.name', 'workers.gender', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function listBasedOnCallingVisa($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_immigration', 'worker_immigration.worker_id', 'workers.id')
            ->where('worker_visa.approval_status', 'Approved')
            ->where([
                'workers.application_id' => $request['application_id'],
                'workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.agent_id' => $request['agent_id'],
                'workers.cancel_status' => 0
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->Where('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date', 'worker_immigration.immigration_status', DB::raw('COUNT(workers.id) as workers'))
            ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date', 'worker_immigration.immigration_status')
            ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
}