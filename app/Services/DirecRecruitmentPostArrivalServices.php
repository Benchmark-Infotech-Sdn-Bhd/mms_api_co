<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerVisa;
use App\Models\WorkerArrival;
use App\Models\DirectrecruitmentArrival;
use App\Models\CancellationAttachment;
use App\Models\Workers;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Events\WorkerQuotaUpdated;
use App\Events\KSMQuotaUpdated;

class DirecRecruitmentPostArrivalServices
{
    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var WorkerArrival
     */
    private WorkerArrival $workerArrival;
    /**
     * @var DirectrecruitmentArrival
     */
    private DirectrecruitmentArrival $directrecruitmentArrival;
    /**
     * @var CancellationAttachment
     */
    private CancellationAttachment $cancellationAttachment;
    /**
     * @var workers
     */
    private Workers $workers;
    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * DirecRecruitmentPostArrivalServices constructor.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param WorkerVisa $workerVisa
     * @param WorkerArrival $workerArrival
     * @param DirectrecruitmentArrival $directrecruitmentArrival
     * @param CancellationAttachment $cancellationAttachment
     * @param Workers $workers
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
     * @param Storage $storage
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, WorkerVisa $workerVisa, WorkerArrival $workerArrival, DirectrecruitmentArrival $directrecruitmentArrival, CancellationAttachment $cancellationAttachment, Workers $workers, DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices, Storage $storage, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry)
    {
        $this->directRecruitmentPostArrivalStatus           = $directRecruitmentPostArrivalStatus;
        $this->workerVisa                                   = $workerVisa;
        $this->workerArrival                                = $workerArrival;
        $this->directrecruitmentArrival                     = $directrecruitmentArrival;
        $this->cancellationAttachment                       = $cancellationAttachment;
        $this->workers                                      = $workers;
        $this->directRecruitmentOnboardingCountryServices   = $directRecruitmentOnboardingCountryServices;
        $this->storage                                      = $storage;
        $this->directRecruitmentOnboardingCountry           = $directRecruitmentOnboardingCountry;
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
    public function postArrivalValidation(): array
    {
        return [
            'arrived_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'entry_visa_valid_until' => 'required|date|date_format:Y-m-d|after:yesterday'
        ];
    }
    /**
     * @return array
     */
    public function jtkSubmissionValidation(): array
    {
        return [
            'jtk_submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow'
        ];
    }
    /**
     * @return array
     */
    public function cancellationValidation(): array
    {
        return [
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * @return array
     */
    public function postponedValidation(): array
    {
        return [
            'new_arrival_date' => 'required|date|date_format:Y-m-d|after:yesterday',
            'arrival_time' => 'required|regex:/^[aAmMpP0-9\: ]*$/',
            'flight_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'remarks' => 'required'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function postArrivalStatusList($request): mixed
    {
        return $this->directRecruitmentPostArrivalStatus
            ->join('directrecruitment_applications', function ($join) use($request) {
                $join->on('directrecruitment_post_arrival_status.application_id', '=', 'directrecruitment_applications.id')
                     ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->select('directrecruitment_post_arrival_status.id', 'directrecruitment_post_arrival_status.item', 'directrecruitment_post_arrival_status.updated_on', 'directrecruitment_post_arrival_status.status')
            ->where([
                'directrecruitment_post_arrival_status.application_id' => $request['application_id'],
                'directrecruitment_post_arrival_status.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->orderBy('directrecruitment_post_arrival_status.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
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
            ->join('worker_arrival', 'worker_arrival.worker_id', 'workers.id')
            ->join('directrecruitment_arrival', 'directrecruitment_arrival.id', 'worker_arrival.arrival_id')
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
                'workers.cancel_status' => 0
            ])
            ->where(function ($query) use ($request) {
                $query->where('worker_arrival.arrival_status', 'Not Arrived')
                ->orWhere('worker_arrival.jtk_submitted_on', NULL);
            })
            ->whereNotNull('worker_arrival.arrival_id')
            ->where('worker_arrival.arrival_status', '!=', 'Postponed')
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['filter']) && !empty($request['filter'])) {
                    $query->where('directrecruitment_arrival.flight_date', $request['filter']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'worker_arrival.jtk_submitted_on', 'worker_arrival.arrival_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
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
     * @return array|bool
     */
    public function updatePostArrival($request): array|bool
    {
        $validator = Validator::make($request, $this->postArrivalValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {

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
                    })->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at')
                    ->find($request['onboarding_country_id']);
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $this->workerArrival->whereIn('worker_id', $request['workers'])->where('arrival_status', '!=', 'Postponed')
                ->update([
                    'arrival_status' => 'Arrived', 
                    'arrived_date' => $request['arrived_date'], 
                    'entry_visa_valid_until' => $request['entry_visa_valid_until'], 
                    'modified_by' => $request['modified_by']
                ]);
            $this->workerVisa->whereIn('worker_id', $request['workers'])
                ->update([
                    'entry_visa_valid_until' => $request['entry_visa_valid_until'], 
                    'modified_by' => $request['modified_by']
                ]);
            $this->workers->whereIn('id', $request['workers'])
            ->update([
                'directrecruitment_status' => 'Arrived', 
                'modified_by' => $request['modified_by']
            ]);
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);

        $onBoardingStatus['application_id'] = $request['application_id'];
        $onBoardingStatus['country_id'] = $request['onboarding_country_id'];
        $onBoardingStatus['onboarding_status'] = 7; //Agent Added
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);

        return true;
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function updateJTKSubmission($request): array|bool
    {
        $validator = Validator::make($request, $this->jtkSubmissionValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {

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
                    })->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at')
                    ->find($request['onboarding_country_id']);
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }
            
            $this->workerArrival->whereIn('worker_id', $request['workers'])
                ->update([
                    'jtk_submitted_on' => $request['jtk_submitted_on'], 
                    'modified_by' => $request['modified_by']
                ]);
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function updateCancellation($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->cancellationValidation());
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
                    })->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at')
                    ->find($request['onboarding_country_id']);
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $this->workerArrival->whereIn('worker_id', $request['workers'])
                ->update([
                    'arrival_status' => 'Cancelled',
                    'remarks' => $request['remarks'] ?? '',
                    'modified_by' => $request['modified_by']
                ]);
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'cancel_status' => Config::get('services.POST_ARRIVAL_CANCELLED_STATUS'), 
                    'remarks' => $request['remarks'] ?? '',
                    'modified_by' => $request['modified_by']
                ]);
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'directrecruitment_status' => 'Cancelled', 
                    'modified_by' => $request['modified_by']
                ]);

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
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function updatePostponed($request): array|bool
    {
        $validator = Validator::make($request, $this->postponedValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {

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
                    })->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at')
                    ->find($request['onboarding_country_id']);
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $this->workerArrival->whereIn('worker_id', $request['workers'])
                ->update(['arrival_status' => 'Postponed', 'remarks' => $request['remarks'] ?? '', 'modified_by' => $request['modified_by']]);
            $arrivalDetails = $this->directrecruitmentArrival->create([
                'application_id' => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id'],
                'item_name' => 'Arrival',
                'flight_date' => $request['new_arrival_date'],
                'arrival_time' => $request['arrival_time'],
                'flight_number' => $request['flight_number'],
                'remarks' => $request['remarks'],
                'status' => 'Not Arrived',
                'created_by' => $request['modified_by'] ?? 0,
                'modified_by' => $request['modified_by'] ?? 0
            ]);
            foreach ($request['workers'] as $workerId) {
                $this->workerArrival->create([
                    'arrival_id' => $arrivalDetails->id,
                    'worker_id' => $workerId,
                    'arrival_status' => 'Not Arrived',
                    'created_by' => $request['modified_by'],
                    'modified_by' => $request['modified_by']
                ]);
            }
            $this->workers->whereIn('id', $request['workers'])->update(['directrecruitment_status' => 'Not Arrived', 'modified_by' => $request['modified_by']]);
        }
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
            ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'workers.id')
            ->leftJoin('directrecruitment_arrival', 'directrecruitment_arrival.id', 'worker_arrival.arrival_id')
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
            ->where(function ($query) use ($request) {
                $query->where('worker_arrival.arrival_status', 'Not Arrived')
                ->orWhere('worker_arrival.jtk_submitted_on', NULL);
            })
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['filter']) && !empty($request['filter'])) {
                    $query->where('directrecruitment_arrival.flight_date', $request['filter']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'worker_arrival.jtk_submitted_on', 'worker_arrival.arrival_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }
}