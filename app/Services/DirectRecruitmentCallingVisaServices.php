<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\CancellationAttachment;
use App\Models\DirectRecruitmentOnboardingCountry;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Events\WorkerQuotaUpdated;
use App\Events\KSMQuotaUpdated;

class DirectRecruitmentCallingVisaServices
{
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var CancellationAttachment
     */
    private CancellationAttachment $cancellationAttachment;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * DirectRecruitmentCallingVisaServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param CancellationAttachment $cancellationAttachment
     * @param Storage $storage
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, Workers $workers, WorkerVisa $workerVisa, CancellationAttachment $cancellationAttachment, Storage $storage, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry)
    {
        $this->directRecruitmentCallingVisaStatus           = $directRecruitmentCallingVisaStatus;
        $this->workers                                      = $workers;
        $this->workerVisa                                   = $workerVisa;
        $this->cancellationAttachment                       = $cancellationAttachment;
        $this->storage                                      = $storage;
        $this->directRecruitmentOnboardingCountry           = $directRecruitmentOnboardingCountry;

    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'calling_visa_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/',
            'submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow'
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
     * @return array
     */
    public function cancelValidation(): array
    {
        return [
            'workers' => 'required'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function callingVisaStatusList($request): mixed
    {
        return $this->directRecruitmentCallingVisaStatus
            ->join('directrecruitment_applications', function ($join) use($request) {
                $join->on('direct_recruitment_calling_visa_status.application_id', '=', 'directrecruitment_applications.id')
                        ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->select('direct_recruitment_calling_visa_status.id', 'direct_recruitment_calling_visa_status.item', 'direct_recruitment_calling_visa_status.updated_on', 'direct_recruitment_calling_visa_status.status')
            ->where([
                'direct_recruitment_calling_visa_status.application_id' => $request['application_id'],
                'direct_recruitment_calling_visa_status.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->orderBy('direct_recruitment_calling_visa_status.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function submitCallingVisa($request): bool|array
    {
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
                                ->where('company_id', $request['company_id'])
                                ->count();
        if($workerCompanyCount != count($request['workers'])) {
            return [
                'InvalidUser' => true
            ];
        }
        $applicationCheck = $this->directRecruitmentOnboardingCountry
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                 ->where('directrecruitment_applications.company_id', $request['company_id']);
        })->find($request['onboarding_country_id']);
        if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
            return [
                'InvalidUser' => true
            ];
        }
        if(isset($request['workers']) && !empty($request['workers']) && !empty($request['calling_visa_reference_number'])) {
            $workerCount = $this->workerVisa->where('calling_visa_reference_number', $request['calling_visa_reference_number'])->count('worker_id');
            $workerCount +=count($request['workers']);
            if($workerCount > Config::get('services.CALLING_VISA_WORKER_COUNT')) {
                return [
                    'workerCountError' => true
                ];
            } else {
                $this->workerVisa->whereIn('worker_id', $request['workers'])->update(['calling_visa_reference_number' => $request['calling_visa_reference_number'], 'submitted_on' => $request['submitted_on'], 'status' => 'Processed', 'modified_by' => $request['modified_by']]);
            }
        }
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['modified_by']]);
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workersList($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        $data = $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->whereIn('worker_visa.status', ['Pending', 'Expired'])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['agent_id']) && !empty($request['agent_id'])) {
                    $query->where('directrecruitment_workers.agent_id', $request['agent_id']);
                }
            });

            if(isset($request['export']) && !empty($request['export']) ){
                $data = $data->select('workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'worker_visa.status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
            }else{
                $data = $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status')->distinct('workers.id')
                ->orderBy('workers.id', 'desc')
                ->paginate(Config::get('services.paginate_worker_row'));
            }
            return $data;
            
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->workers->with(['workerBioMedical' => function ($query) { 
                $query->select(['id', 'worker_id', 'bio_medical_valid_until']);
            }])->with(['workerVisa' => function ($query) {
                $query->select(['id', 'worker_id', 'ksm_reference_number', 'calling_visa_reference_number', 'submitted_on', 'status']);
            }])->where('workers.id', $request['worker_id'])
            ->whereIn('company_id', $request['company_id'])
            ->select('id', 'name', 'passport_number')
            ->get();
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function cancelWorker($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->cancelValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);

            $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
                                ->where('company_id', $request['company_id'])
                                ->count();
            if($workerCompanyCount != count($request['workers'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $applicationCheck = $this->directRecruitmentOnboardingCountry
                    ->join('directrecruitment_applications', function ($join) use($request) {
                        $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                            ->where('directrecruitment_applications.company_id', $request['company_id']);
                    })->find($request['onboarding_country_id']);
            if(is_null($applicationCheck) || $applicationCheck->application_id != $request['application_id']) {
                return [
                    'InvalidUser' => true
                ];
            }
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'directrecruitment_status' => 'Cancelled',
                    'cancel_status' => 1, 
                    'remarks' => $request['remarks'] ?? '',
                    'modified_by' => $request['modified_by']
                ]);

            if(request()->hasFile('attachment')) {
                foreach ($request['workers'] as $workerId) {
                    foreach($request->file('attachment') as $file) {
                        $fileName = $file->getClientOriginalName();
                        $filePath = 'directRecruitment/workers/cancellation/' . $workerId. '/'. $fileName; 
                        $linode = $this->storage::disk('linode');
                        $linode->put($filePath, file_get_contents($file));
                        $fileUrl = $this->storage::disk('linode')->url($filePath);    
                        $this->cancellationAttachment->create([
                            'file_id' => $workerId,
                            'file_name' => $fileName,
                            'file_type' => 'Cancellation Letter',
                            'file_url' => $fileUrl,
                            'created_by' => $request['modified_by'],
                            'modified_by' => $request['modified_by']
                        ]);
                    }
                }
            }

            $workerDetails = [];
            $ksmCount = [];

            // update utilised quota based on ksm reference number
            foreach($request['workers'] as $worker) {
                $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first(['ksm_reference_number']);
                $workerDetails[$worker] = $ksmDetails->ksm_reference_number;
            }
            $ksmCount = array_count_values($workerDetails);
            foreach($ksmCount as $key => $value) {
                event(new KSMQuotaUpdated($request['onboarding_country_id'], $key, $value, 'decrement'));
            }

            // update utilised quota in onboarding country
            event(new WorkerQuotaUpdated($request['onboarding_country_id'], count($request['workers']), 'decrement'));
        }
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['modified_by']]);
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workerListForCancellation($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        $data = $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->whereNull('workers.replace_worker_id')
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['calling_visa_reference_number']) && !empty($request['calling_visa_reference_number'])) {
                    $query->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number']);
                }
            });
            
            if(isset($request['export']) && !empty($request['export']) ){
                $data = $data->select('workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'workers.cancel_status')->distinct('workers.id')
                ->orderBy('workers.id', 'desc')
                ->get();
            }else{
                $data = $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'workers.cancel_status')->selectRaw("(CASE WHEN workers.cancel_status = 1 THEN 'Cancelled' ELSE '' END) AS status")->distinct('workers.id')
                ->orderBy('workers.id', 'desc')
                ->paginate(Config::get('services.paginate_worker_row'));
            }
            return $data;
    }
}