<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerPLKSAttachments;
use App\Models\Workers;
use App\Models\DirectrecruitmentApplications;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirectRecruitmentPostArrivalPLKSServices
{
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var WorkerPLKSAttachments
     */
    private WorkerPLKSAttachments $workerPLKSAttachments;
    /**
     * @var workers
     */
    private Workers $workers;
    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * DirectRecruitmentPostArrivalFomemaServices constructor.
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param WorkerPLKSAttachments $workerPLKSAttachments
     * @param Workers $workers
     * @param Storage $storage
     */
    public function __construct(DirectrecruitmentApplications $directrecruitmentApplications, DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, WorkerPLKSAttachments $workerPLKSAttachments, Workers $workers, Storage $storage)
    {
        $this->directrecruitmentApplications        = $directrecruitmentApplications;
        $this->directRecruitmentPostArrivalStatus   = $directRecruitmentPostArrivalStatus;
        $this->workerPLKSAttachments                = $workerPLKSAttachments;
        $this->workers                              = $workers;
        $this->storage                              = $storage;
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
    public function createValidation(): array
    {
        return [
            'plks_expiry_date' => 'required|date|date_format:Y-m-d|after:yesterday',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * @param $applicationId, $onboardingCountryId, $modifiedBy
     * @return void
     */
    public function updatePostArrivalStatus($applicationId, $onboardingCountryId, $modifiedBy): void
    {
        $this->directRecruitmentPostArrivalStatus->where([
            'application_id' => $applicationId,
            'onboarding_country_id' => $onboardingCountryId
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $modifiedBy]);
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
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_fomema.fomema_status' => 'Fit'
            ])
            ->whereIn('workers.special_pass', [0,2])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'workers.fomema_valid_until', 'workers.special_pass_valid_until', 'workers.plks_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function updatePLKS($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'directrecruitment_status' => 'Processed',
                    'plks_status' => 'Approved', 
                    'plks_expiry_date' => $request['plks_expiry_date'], 
                    'modified_by' => $request['modified_by']
                ]);
            if(request()->hasFile('attachment')) {
                foreach ($request['workers'] as $workerId) {
                    foreach($request->file('attachment') as $file) {
                        $fileName = $file->getClientOriginalName();
                        $filePath = 'directRecruitment/workers/plks/' . $workerId. '/'. $fileName; 
                        $linode = $this->storage::disk('linode');
                        $linode->put($filePath, file_get_contents($file));
                        $fileUrl = $this->storage::disk('linode')->url($filePath);
                        $this->workerPLKSAttachments->create([
                            'file_id' => $workerId,
                            'file_name' => $fileName,
                            'file_type' => 'PLKS',
                            'file_url' => $fileUrl,
                            'created_by' => $request['modified_by'],
                            'modified_by' => $request['modified_by']
                        ]);
                    }
                }
            }
        }
        $this->directrecruitmentApplications->where('id', $request['application_id'])->update(['onboarding_flag' => 1]);

        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workersListExport($request): mixed
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
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_fomema.fomema_status' => 'Fit'
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'workers.fomema_valid_until', 'workers.special_pass_valid_until', 'workers.plks_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }
}