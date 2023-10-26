<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\DirectrecruitmentArrival;
use App\Models\WorkerArrival;
use App\Models\CancellationAttachment;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use App\Models\DirectRecruitmentPostArrivalStatus;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DirectRecruitmentArrivalServices
{
    /**
     * @var workers
     */
    private Workers $workers;

    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;

    /**
     * @var DirectrecruitmentArrival
     */
    private DirectrecruitmentArrival $directrecruitmentArrival;

    /**
     * @var WorkerArrival
     */
    private WorkerArrival $workerArrival;

    /**
     * @var CancellationAttachment
     */
    private CancellationAttachment $cancellationAttachment;

    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;

    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;

    /**
     * @var Storage
     */
    private Storage $storage;
    

    /**
     * DirectRecruitmentArrivalServices constructor.
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param DirectrecruitmentArrival $directrecruitmentArrival
     * @param WorkerArrival $workerArrival
     * @param CancellationAttachment $cancellationAttachment
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param Storage $storage;
     */
    public function __construct(Workers $workers, WorkerVisa $workerVisa, DirectrecruitmentArrival $directrecruitmentArrival, WorkerArrival $workerArrival, CancellationAttachment $cancellationAttachment, DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices, DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, Storage $storage)
    {
        $this->workers                                      = $workers;
        $this->workerVisa                                   = $workerVisa;
        $this->directrecruitmentArrival                     = $directrecruitmentArrival;
        $this->workerArrival                                = $workerArrival;
        $this->cancellationAttachment                       = $cancellationAttachment;
        $this->directRecruitmentOnboardingCountryServices   = $directRecruitmentOnboardingCountryServices;
        $this->directRecruitmentPostArrivalStatus           = $directRecruitmentPostArrivalStatus;
        $this->storage                                      = $storage;
    }
    /**
     * @return array
     */
    public function submitValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'onboarding_country_id' => 'required',
                'flight_date' => 'required|date|date_format:Y-m-d',
                'arrival_time' => 'required',
                'flight_number' => 'required'
            ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'arrival_id' => 'required',
            'flight_date' => 'required|date|date_format:Y-m-d',
            'arrival_time' => 'required',
            'flight_number' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function updateWorkersValidation(): array
    {
        return [
            'arrival_id' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function cancelValidation(): array
    {
        return [
            'arrival_id' => 'required'
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
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->workerArrival
        ->leftJoin('directrecruitment_arrival', 'worker_arrival.arrival_id', 'directrecruitment_arrival.id')
        ->where([
            ['directrecruitment_arrival.application_id', $request['application_id']],
            ['directrecruitment_arrival.onboarding_country_id', $request['onboarding_country_id']]
        ])
        ->where('worker_arrival.arrival_status', '!=', 'Postponed')
        ->select('directrecruitment_arrival.id', 'directrecruitment_arrival.application_id', 'directrecruitment_arrival.onboarding_country_id', 'directrecruitment_arrival.item_name', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time', 'directrecruitment_arrival.flight_number', 'worker_arrival.arrival_status', DB::raw('COUNT(worker_arrival.worker_id) as workers'))
        ->groupBy('directrecruitment_arrival.id', 'directrecruitment_arrival.application_id', 'directrecruitment_arrival.onboarding_country_id', 'directrecruitment_arrival.item_name', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time', 'directrecruitment_arrival.flight_number', 'worker_arrival.arrival_status')
        ->orderBy('directrecruitment_arrival.id','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->directrecruitmentArrival->where('id', $request['arrival_id'])
        ->select('application_id', 'onboarding_country_id', 'item_name', 'flight_date', 'arrival_time', 'flight_number', 'status', 'remarks')->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workersListForSubmit($request): mixed
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
        ->join('worker_visa', function ($join) {
            $join->on('worker_visa.worker_id', '=', 'workers.id')
            ->where('worker_visa.dispatch_status', '=', 'Processed');
        })
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
        ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'worker_visa.worker_id')
        ->whereIn('workers.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if ($request['user']['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
            }
        })
        ->where([
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
            ['workers.cancel_status', 0]
        ])
        ->whereNull('worker_arrival.arrival_id')
        ->where(function ($query) use ($request) {
            if(isset($request['calling_visa_reference_number']) && !empty($request['calling_visa_reference_number'])) {
                $query->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number']);
            }
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('workers.name', 'like', '%'.$request['search'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('workers.id', 'workers.name', 'workers.gender', 'workers.date_of_birth', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id','directrecruitment_workers.agent_id', 'worker_visa.ksm_reference_number','worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on')
        ->distinct('workers.id')
        ->orderBy('workers.id','DESC')
        ->paginate(Config::get('services.paginate_worker_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workersListForUpdate($request): mixed
    {
        
        return $this->workers
        ->join('worker_visa', function ($join) {
            $join->on('worker_visa.worker_id', '=', 'workers.id')
            ->where('worker_visa.dispatch_status', '=', 'Processed');
        })
        ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'worker_visa.worker_id')
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
        ->whereIn('workers.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if ($request['user']['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
            }
        })
        ->where([
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
            ['worker_arrival.arrival_id', $request['arrival_id']]
        ])
        ->where(function ($query) use ($request) {
            if(isset($request['calling_visa_reference_number']) && !empty($request['calling_visa_reference_number'])) {
                $query->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number']);
            }
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('workers.name', 'like', '%'.$request['search'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('workers.id', 'workers.name', 'workers.gender', 'workers.date_of_birth', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.ksm_reference_number','worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_arrival.arrival_status')
        ->distinct('workers.id')
        ->orderBy('workers.id','DESC')
        ->paginate(Config::get('services.paginate_worker_row'));
    }
    
    /**
     * @param $request
     * @return bool|array
     */
    public function submit($request): bool|array
    {
        $validator = Validator::make($request, $this->submitValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {

            $directrecruitmentArrival = $this->directrecruitmentArrival->create([
                'application_id' => $request['application_id'] ?? 0,
                'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
                'item_name' => 'Arrival',
                'flight_date' => $request['flight_date'],
                'arrival_time' => $request['arrival_time'],
                'flight_number' => $request['flight_number'],
                'remarks' => $request['remarks'],
                'status' => $request['status'] ?? 'Not Arrived', 
                'created_by' => $request['created_by'] ?? 0,
                'modified_by' => $request['created_by'] ?? 0
            ]);

            $request['arrival_id'] = $directrecruitmentArrival->id ?? 0;

            foreach ($request['workers'] as $workerId) {
                $this->workerArrival->updateOrCreate(
                    [
                        'worker_id' => $workerId, 
                        'arrival_id' => $request['arrival_id']
                    ],
                    [
                        'arrival_status' => $request['status'] ?? 'Not Arrived', 
                        'created_by' => $request['created_by'],
                         'modified_by' => $request['created_by']
                    ]
                );
            }

            $this->directRecruitmentPostArrivalStatus->create([
                'application_id' => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id'],
                'item' => 'Post Arrival',
                'updated_on' => Carbon::now(),
                'created_by' => $request['created_by'],
                'modified_by' => $request['created_by']
            ]);

            $this->workers->whereIn('id', $request['workers'])->update(['directrecruitment_status' => 'Not Arrived', 'modified_by' => $request['created_by']]);

            $onBoardingStatus['application_id'] = $request['application_id'];
            $onBoardingStatus['country_id'] = $request['onboarding_country_id'];
            $onBoardingStatus['onboarding_status'] = 6; //Agent Added
            $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);            

            return true;
        }else{
            return false;
        }
        
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $directrecruitmentArrival = $this->directrecruitmentArrival->findOrFail($request['arrival_id']);
        
        $directrecruitmentArrival->flight_date =  $request['flight_date'] ?? $directrecruitmentArrival->flight_date;
        $directrecruitmentArrival->arrival_time =  $request['arrival_time'] ?? $directrecruitmentArrival->arrival_time;
        $directrecruitmentArrival->flight_number =  $request['flight_number'] ?? $directrecruitmentArrival->flight_number;
        $directrecruitmentArrival->status =  $request['status'] ?? $directrecruitmentArrival->status;
        $directrecruitmentArrival->remarks =  $request['remarks'] ?? $directrecruitmentArrival->remarks;
        $directrecruitmentArrival->modified_by =  $request['modified_by'] ?? $directrecruitmentArrival->modified_by;
        $directrecruitmentArrival->save();
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function cancelWorker($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->cancelValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        if(isset($request['workers']) && !empty($request['workers'])) {

            $workers = explode(",", $request['workers']);

            if(is_array($workers)){

                if (request()->hasFile('attachment')){
                    foreach($request->file('attachment') as $file){
                        $fileName = $file->getClientOriginalName();
                        $filePath = '/directRecruitment/workers/cancellation/' . $fileName; 
                        $linode = $this->storage::disk('linode');
                        $linode->put($filePath, file_get_contents($file));
                        $fileUrl = $this->storage::disk('linode')->url($filePath);  
                    }
                }else{
                    $fileName = '';
                    $fileUrl = '';
                }
    
                $this->workerArrival
                ->whereIn('worker_id', $workers)
                ->where('arrival_id',  $request['arrival_id'])
                ->update(
                    ['arrival_status' => 'Cancelled', 
                    'updated_at' => Carbon::now(),
                    'modified_by' => $params['created_by']]);

                $this->workers->whereIn('id', $workers)
                    ->update([
                        'directrecruitment_status' => 'Cancelled',
                        'cancel_status' => 1, 
                        'remarks' => $request['remarks'] ?? '',
                        'modified_by' => $params['created_by']
                    ]);
    
                foreach ($workers as $workerId) {
    
                    if(!empty($fileName) && !empty($fileUrl)){
                        $this->cancellationAttachment->updateOrCreate(
                            ['file_id' => $workerId],
                            ["file_name" => $fileName,
                            "file_type" => 'Arrival Cancellation Letter',
                            "file_url" =>  $fileUrl,
                            "remarks" => $request['remarks'] ?? ''
                        ]);
                    }
    
                }
                return true;

            } else{
                return false;
            }

        }else{
            return false;
        }
    }
    /**
     * @param $request
     * @return mixed
     */
    public function cancelWorkerDetail($request): mixed
    {
        return $this->cancellationAttachment
        ->where('file_id', $request['worker_id'])
        ->where('file_type', 'Arrival Cancellation Letter')
        ->orWhere('file_type', 'Cancellation Letter')
        ->select('file_id', 'file_name', 'file_url', 'remarks')
        ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function callingvisaReferenceNumberList($request): mixed
    {
        return $this->workers
        ->join('worker_visa', function ($join) {
            $join->on('worker_visa.worker_id', '=', 'workers.id')
            ->where('worker_visa.approval_status', '=', 'Approved');
        })
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
        ->whereIn('workers.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if ($request['user']['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
            }
        })
        ->where([
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']]
        ])
        ->select('worker_visa.calling_visa_reference_number')
        ->distinct('worker_visa.calling_visa_reference_number')
        ->get();
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function updateWorkers($request): bool|array
    {
        $validator = Validator::make($request, $this->updateWorkersValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {

            foreach ($request['workers'] as $workerId) {
                $this->workerArrival->updateOrCreate(
                    [
                        'worker_id' => $workerId, 
                        'arrival_id' => $request['arrival_id']
                    ],
                    [
                        'arrival_status' => $request['status'] ?? 'Not Arrived', 
                        'created_by' => $request['modified_by'] ?? 0,
                        'modified_by' => $request['modified_by'] ?? 0
                    ]);
            }
            return true;
        }else{
            return false;
        }
        
    }
     /**
     * @param $request
     * @return mixed
     */
    public function arrivalDateDropDown($request): mixed
    {
        return $this->directrecruitmentArrival
        ->where('application_id', $request['application_id'])
        ->where('onboarding_country_id', $request['onboarding_country_id'])
        ->select( 'flight_date')
        ->distinct()
        ->get();
    }
    
}