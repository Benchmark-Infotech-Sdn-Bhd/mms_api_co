<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerArrival;
use App\Models\DirectrecruitmentWorkers;
use App\Models\DirectRecruitmentOnboardingCountry;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DirectRecruitmentPostponedServices
{
    /**
     * @var workers
     */
    private Workers $workers;
    /**
     * @var WorkerArrival
     */
    private WorkerArrival $workerArrival;
    /**
     * @var DirectrecruitmentWorkers
     */
    private DirectrecruitmentWorkers $directrecruitmentWorkers;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * DirectRecruitmentPostponedServices constructor.
     * @param Workers $workers
     * @param WorkerArrival $workerArrival
     * @param DirectrecruitmentWorkers $directrecruitmentWorkers
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(Workers $workers, WorkerArrival $workerArrival, DirectrecruitmentWorkers $directrecruitmentWorkers, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry)
    {
        $this->workers = $workers;
        $this->workerArrival = $workerArrival;
        $this->directrecruitmentWorkers = $directrecruitmentWorkers;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
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
            ->leftjoin('worker_arrival', 'worker_arrival.worker_id', '=', 'workers.id')
            ->leftjoin('directrecruitment_arrival', 'directrecruitment_arrival.id', '=', 'worker_arrival.arrival_id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_arrival.arrival_status' => 'Postponed'
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_arrival.flight_number', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time')
            ->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }
    /**
     * @param $request
     * @return bool
     */
    public function updateCallingVisaExpiry(): bool
    {
        $postponedWorkerIds = $this->workerArrival
                                ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_arrival.worker_id')
                                ->where('worker_arrival.arrival_status', 'Postponed')
                                ->where('worker_visa.calling_visa_valid_until', '<', Carbon::now()->format('Y-m-d'))
                                ->select('worker_arrival.worker_id')
                                ->distinct('worker_arrival.worker_id')
                                ->get()->toArray();
        $postponedWorkerIds = array_column($postponedWorkerIds, 'worker_id');
        if(!empty($postponedWorkerIds)) {
            $workerIds = $this->workerArrival
                    ->whereIn('worker_id', $postponedWorkerIds)
                    ->where('arrival_status', 'Not Arrived')
                    ->select('worker_id')
                    ->distinct('worker_id')
                    ->get()->toArray();
            $workerIds = array_column($workerIds, 'worker_id');
            
            if(isset($workerIds) && !empty($workerIds)) {
                $this->workers->whereIn('id', $workerIds)
                    ->update([
                        'directrecruitment_status' => 'Expired'
                    ]);
                foreach ($workerIds as $workerId) {
                    $utilisedQuota = 0;
                    $onBoardingCountryDetails = $this->directrecruitmentWorkers
                                            ->leftJoin('workers', 'workers.id', 'directrecruitment_workers.worker_id')
                                            ->where('directrecruitment_workers.worker_id', $workerId)
                                            ->where('workers.directrecruitment_status', 'Expired')
                                            ->select('directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id')
                                            ->get()->toArray();
                                            
                    $countryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($onBoardingCountryDetails[0]['onboarding_country_id']);
                    $utilisedQuota = (($countryDetails->utilised_quota - 1) < 0) ? 0 : $countryDetails->utilised_quota - 1;
                    $countryDetails->utilised_quota = $utilisedQuota;
                    $countryDetails->save();
                }
            }
        }        
        return true;
    }
}