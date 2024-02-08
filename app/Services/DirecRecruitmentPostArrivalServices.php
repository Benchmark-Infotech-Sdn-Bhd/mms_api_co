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
    public const CUSTOMER = 'Customer';
    public const STATUS_NOT_ARRIVED = 'Not Arrived';
    public const STATUS_ARRIVED = 'Arrived';
    public const ARRIVAL = 'Arrival';
    
    public const STATUS_POSTPONED = 'Postponed';
    public const STATUS_CANCELLED = 'Cancelled';
    public const ONBOARDING_STATUS = 7;
    public const REQUEST_COMPANY_ID = 'company_id';
    public const STATUS_ACTIVE = 1;
    public const STATUS_IN_ACTIVE = 0;

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
    public function __construct(
        DirectRecruitmentPostArrivalStatus          $directRecruitmentPostArrivalStatus, 
        WorkerVisa                                  $workerVisa, 
        WorkerArrival                               $workerArrival, 
        DirectrecruitmentArrival                    $directrecruitmentArrival, 
        CancellationAttachment                      $cancellationAttachment, 
        Workers                                     $workers, 
        DirectRecruitmentOnboardingCountryServices  $directRecruitmentOnboardingCountryServices, 
        Storage                                     $storage, 
        DirectRecruitmentOnboardingCountry          $directRecruitmentOnboardingCountry
    )
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
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }

    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function postArrivalValidation(): array
    {
        return [
            'arrived_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'entry_visa_valid_until' => 'required|date|date_format:Y-m-d|after:yesterday'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function jtkSubmissionValidation(): array
    {
        return [
            'jtk_submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function cancellationValidation(): array
    {
        return [
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
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
     * Lists the post arrival status list.
     *
     * @param array $request The request data containing post arrival status list.
     * @return mixed Returns list of post arrival status.
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
     * Returns a paginated list of workers with their post arrival details.
     *
     * @param array $request The request data containing company id,  application_id, onboarding_country_id, user details with search filters
     * @return mixed \Illuminate\Pagination\LengthAwarePaginator The paginated list of workers with their post arrival details.
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
            ->whereIn('workers.company_id', $request[self::REQUEST_COMPANY_ID])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == self::CUSTOMER) {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.cancel_status' => self::STATUS_IN_ACTIVE
            ])
            ->where(function ($query) use ($request) {
                $query->where('worker_arrival.arrival_status', self::STATUS_NOT_ARRIVED)
                ->orWhere('worker_arrival.jtk_submitted_on', NULL);
            })
            ->whereNotNull('worker_arrival.arrival_id')
            ->where('worker_arrival.arrival_status', '!=', self::STATUS_POSTPONED)
            ->where(function ($query) use ($request) {
                $this->applySearchQuery($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'worker_arrival.jtk_submitted_on', 'worker_arrival.arrival_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Applies the search query to the given query builder.
     *
     * @param Builder $query The query builder to apply the search query to.
     * @param array $request The request containing the search parameter.
     * @return void
     */
    private function applySearchQuery($query, $request)
    {
        if (!empty($request['search'])) {
            $query->where('workers.name', 'like', '%'.$request['search'].'%')
            ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
            ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
        }
    }

    /**
     * Applies the search query to the given query builder.
     *
     * @param Builder $query The query builder to apply the search query to given filters.
     * @param array $request The request containing the search parameter.
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        if (!empty($request['filter'])) {
            $query->where('directrecruitment_arrival.flight_date', $request['filter']);
        }
    }

    /**
     * Update the post arrival updatetion details.
     *
     * @param $applicationId, $onboardingCountryId, $modifiedBy
     * Updated the post arrival information
     */
    public function updatePostArrivalStatus(int $applicationId, int $onboardingCountryId, int $modifiedBy): void
    {
        $this->directRecruitmentPostArrivalStatus->where([
            'application_id' => $applicationId,
            'onboarding_country_id' => $onboardingCountryId
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $modifiedBy]);
    }

    /**
     * Get the count of workers in the given company id
     *
     * @param mixed $request
     * Return the worker count as interger return
     */
    public function checkWorkerCount(mixed $request): int
    {
        return $this->workers->whereIn('id', $request['workers'])
                ->where('company_id', $request['company_id'])
                ->count();
    }

    /**
     * Get the requested Application id belongs to the user company
     *
     * @param mixed $request
     * Return the worker count as interger return
     */
    public function checkApplication(mixed $request): object
    {
        return $this->directRecruitmentOnboardingCountry
                ->join('directrecruitment_applications', function ($join) use($request) {
                    $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                        ->where('directrecruitment_applications.company_id', $request['company_id']);
                })->find($request['onboarding_country_id']);
    }

    /**
     * Update the worker arrival details.
     *
     * @param $workersId, $arrivedDate, $entryVisaVaidUntil, $modifiedBy
     * Updated the worker arrival information
     */
    public function updateWorkerArrivalDetails(array $workersId, string $arrivedDate, string $entryVisaVaidUntil, int $modifiedBy): void
    {
        $this->workerArrival->whereIn('worker_id', $workersId)->where('arrival_status', '!=', self::STATUS_POSTPONED)
        ->update([
            'arrival_status' => self::STATUS_ARRIVED, 
            'arrived_date' => $arrivedDate, 
            'entry_visa_valid_until' => $entryVisaVaidUntil, 
            'modified_by' => $modifiedBy
        ]);
    }

    /**
     * Update the worker visa details.
     *
     * @param $applicationId, $entryVisaVaidUntil, $modifiedBy
     * Updated the worker visa information
     */
    public function updateWorkerVisaDetails(array $workersId, string $entryVisaVaidUntil, int $modifiedBy): void
    {
        $this->workerVisa->whereIn('worker_id', $workersId)
        ->update([
            'entry_visa_valid_until' => $entryVisaVaidUntil, 
            'modified_by' => $modifiedBy
        ]);
    } 
    
    /**
     * Update the worker Direct Recruitment Status.
     *
     * @param $workersId, $modifiedBy
     * Updated the worker Direct Recruitment Status.
     */
    public function updateWorkerDirectRecruitmentStatus(array $workersId, int $modifiedBy): void
    {
        $this->workers->whereIn('id', $workersId)
        ->update([
            'directrecruitment_status' => self::STATUS_ARRIVED, 
            'modified_by' => $modifiedBy
        ]);
    } 

    /**
     * Update the Direct Recruitment Onboarding Status on the application id.
     *
     * @param $applicationId, $onboardingCountryId
     * Updated the worker Direct Recruitment Onboarding Status.
     */
    public function updateDirectRecruitmentOnboardingStatus(int $applicationId, int $onboardingCountryId): void
    {
        $onBoardingStatus['application_id'] = $applicationId;
        $onBoardingStatus['country_id'] = $onboardingCountryId;
        $onBoardingStatus['onboarding_status'] = self::ONBOARDING_STATUS; //Agent Added
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);
    } 

    /**
     * Update the JTK Submitted Date Details.
     *
     * @param $workersId, $jtkSubmittedOn, $modifiedBy
     * Updated the JTK Submitted Date Details.
     */
    public function updateJtkSubmittedDetails(array $workersId, string $jtkSubmittedOn, int $modifiedBy): void
    {
        $this->workerArrival->whereIn('worker_id', $workersId)
        ->update([
            'jtk_submitted_on' => $jtkSubmittedOn, 
            'modified_by' => $modifiedBy
        ]);
    } 

    /**
     * Update the Worker Arrival Status Update
     *
     * @param $workersId, $arrivalStatus, $remarks, $modifiedBy
     * Updated the Worker Arrival Status Update.
     */
    public function updateWorkerArrivalStatusDetails(array $workersId, string $arrivalStatus, string $remarks, int $modifiedBy): void
    {
        $this->workerArrival->whereIn('worker_id', $workersId)
        ->update([
            'arrival_status' => $arrivalStatus, 
            'remarks' => $remarks ?? '', 
            'modified_by' => $modifiedBy
        ]);
    }     
    
    /**
     * Update the post arraival details on the given input request.
     *
     * @param array $request The request data to update the post arrival details.
     * @return array|bool Returns an array with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "isInvalidUser": A boolean returns true if user access invalid application which is not not in his beloning company
     *  - Update worker arrival details with arrrived date and entry visa valid until details
     *  - Update worker visa table with entry visa valid until details
     *  - Update worker table with directrecruitment status
     *  - Update worker post arrival details with application id and onboarding country id
     *  - Update Direct Recruitment OnBoarding details with application id and onboarding country id
     *  - "isUpdated": A boolean indicating if the post arrival details was successfully updated.
     */
    public function updatePostArrival($request): array|bool
    {
        $validator = Validator::make($request, $this->postArrivalValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(!empty($request['workers'])) {

            $workerCompanyCount = $this->checkWorkerCount($request);                                
            if(isset($workerCompanyCount) && ($workerCompanyCount != count($request['workers']))) {
                return [
                    'InvalidUser' => true
                ];
            }

            $applicationCheck = $this->checkApplication($request);            
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $this->updateWorkerArrivalDetails($request['workers'], $request['arrived_date'], $request['entry_visa_valid_until'], $request['modified_by']);

            $this->updateWorkerVisaDetails($request['workers'], $request['entry_visa_valid_until'], $request['modified_by']);

            $this->updateWorkerDirectRecruitmentStatus($request['workers'], $request['modified_by']);
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);

        $this->updateDirectRecruitmentOnboardingStatus($request['application_id'], $request['onboarding_country_id']);

        return true;
    }
    
    /**
     * Update the JTK Submission details on the given input request.
     *
     * @param array $request The request data to update the JTK Submission details.
     * @return array|bool Returns an array with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "isInvalidUser": A boolean returns true if user access invalid application which is not not in his beloning company
     *  - Update worker arrival details with JTK Submission date details
     *  - Update worker post arrival details with application id and onboarding country id
     *  - "isUpdated": A boolean indicating if the post arrival details was successfully updated.
     */
    public function updateJTKSubmission($request): array|bool
    {
        $validator = Validator::make($request, $this->jtkSubmissionValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(!empty($request['workers'])) {

            $workerCompanyCount = $this->checkWorkerCount($request);                                
            if(isset($workerCompanyCount) && ($workerCompanyCount != count($request['workers']))) {
                return [
                    'InvalidUser' => true
                ];
            }

            $applicationCheck = $this->checkApplication($request);            
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }
            
            $this->updateJtkSubmittedDetails($request['workers'], $request['jtk_submitted_on'], $request['modified_by']);
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }
    /**
     * Update the Arrival Cancellation on the given input request.
     *
     * @param array $request The request data to update the Arrival Cancellation details.
     * @return array|bool Returns an array with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "isInvalidUser": A boolean returns true if user access invalid application which is not not in his beloning company
     *  - Update worker arrival details with Arrival Cancellation details
     *  - Update worker post arrival details with Arrival Cancellation details, remarks and directrecruitment_status
     *  - update utilised quota based on ksm reference number
     *  - update utilised quota in onboarding country
     *  - Update the Cancellaion attachment details against ech workers applied for the Cancellaion
     *  - "isUpdated": A boolean indicating if the post arrival details was successfully updated.
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

            $workerCompanyCount = $this->checkWorkerCount($request);
            
            if(isset($workerCompanyCount) && ($workerCompanyCount != count($request['workers']))) {
                return [
                    'InvalidUser' => true
                ];
            }

            $applicationCheck = $this->checkApplication($request);            
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $this->updateWorkerArrivalStatusDetails($request['workers'], self::STATUS_CANCELLED, $request['remarks'] ?? '', $request['modified_by']);

            $this->updateWorkerCancelStatusDetails($request['workers'], self::STATUS_CANCELLED, $request['remarks'] ?? '', $request['modified_by']);

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
                $this->uploadFiles($request->file('attachment'), $workerId);
            }
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }

    /**
     * Upload multiple files for worker Cancellation.
     *
     * @param array $files A array of files to be uploaded.
     * @param int $workerId The ID of the worker to associate the files with.
     *
     * @return void
     */
    public function uploadFiles($files, $workerId)
    {
        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = 'directRecruitment/workers/cancellation/' . $workerId. '/'. $fileName; 

            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));

            $fileUrl = $linode->url($filePath);

            $this->cancellationAttachment::create([
                "file_id" => $workerId,
                "file_name" => $fileName,
                "file_type" => 'Cancellation Letter',
                "file_url" => $fileUrl
            ]);
        }
    }

    /**
     * Update the Worker Arrival Cancel Status Update
     *
     * @param $workersId, $arrivalStatus, $remarks, $modifiedBy
     * Updated the Worker Arrival Cancel Status Update.
     */
    public function updateWorkerCancelStatusDetails(array $workersId, string $arrivalStatus, string $remarks, int $modifiedBy): void
    {
        $this->workers->whereIn('id', $workersId)
            ->update([
                'cancel_status' => Config::get('services.POST_ARRIVAL_CANCELLED_STATUS'), 
                'directrecruitment_status' => $arrivalStatus, 
                'remarks' => $remarks ?? '',
                'modified_by' => $modifiedBy
            ]);
    }   
    
    /**
     * Update the Worker Arrival Postponed details on the given input request.
     *
     * @param array $request The request data to update the Worker Arrival Postponed details.
     * @return array|bool Returns an array with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "isInvalidUser": A boolean returns true if user access invalid application which is not not in his beloning company
     *  - Update worker arrival details with JTK Submission date details
     *  - Update worker post arrival details with application id and onboarding country id
     *  - "isUpdated": A boolean indicating if the post arrival details was successfully updated.
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

            $workerCompanyCount = $this->checkWorkerCount($request);                                
            if(isset($workerCompanyCount) && ($workerCompanyCount != count($request['workers']))) {
                return [
                    'InvalidUser' => true
                ];
            }

            $applicationCheck = $this->checkApplication($request);             
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $this->updateWorkerArrivalStatusDetails($request['workers'], self::STATUS_POSTPONED, $request['remarks'], $request['modified_by']);

            $arrivalDetails = $this->createDirectRecruitmentArrival($request);
            foreach ($request['workers'] as $workerId) {
                $this->createWorkerArrival($arrivalDetails->id, $workerId, $request['modified_by']);
            }

            $this->updateWorkerNotArrivedStatus($request['workers'], $request['modified_by']);
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }

    /**
     * Create a new Direct Recruitment Arrival .
     *
     * @param array $inputData The data used to create the Direct Recruitment Arrival.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: OnBoarding Country Id.
     * - new_arrival_date: New Arrival date of the worker.
     * - arrival_time: The arrival time of the worker.
     * - flight_number: Worker arriving flight number information.
     * - remarks: Remarks of the postponed arrival details.
     * - created_by: The user who created the Direct Recruitment Arrival.
     * - modified_by: The user who modified the Direct Recruitment Arrival.
     *
     * @return mixed The newly created Direct Recruitment Arrival.
     */
    public function createDirectRecruitmentArrival(array $request)
    {
        return $this->directrecruitmentArrival->create([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id'],
            'item_name' => self::ARRIVAL,
            'flight_date' => $request['new_arrival_date'],
            'arrival_time' => $request['arrival_time'],
            'flight_number' => $request['flight_number'],
            'remarks' => $request['remarks'],
            'status' => self::STATUS_NOT_ARRIVED,
            'created_by' => $request['modified_by'] ?? 0,
            'modified_by' => $request['modified_by'] ?? 0
        ]);
    }

    /**
     * Create a new Worker Arrival .
     *
     * @param array $inputData The data used to create the Direct Recruitment Arrival.
     * The array should contain the following keys:
     * - arrival_id: Direct Recruitment Arrival Id.
     * - worker_id: Not Arrived Worker Id.
     * - created_by: The user who created the Direct Recruitment Arrival.
     * - modified_by: The user who modified the Direct Recruitment Arrival.
     *
     * @return void 
     */
    public function createWorkerArrival(int $arrivalDetailsId, int $workerId, int $modifiedBy): void
    {
        $this->workerArrival->create([
            'arrival_id' => $arrivalDetailsId,
            'worker_id' => $workerId,
            'arrival_status' => self::STATUS_NOT_ARRIVED,
            'created_by' => $modifiedBy,
            'modified_by' => $modifiedBy
        ]);
    }

    /**
     * Update the Worker Not Arrived Status Details.
     *
     * @param $workersId, $modifiedBy
     * Updated the worker Not Arrived Status Details against the particular worker.
     */
    public function updateWorkerNotArrivedStatus(array $workersId, int $modifiedBy): void
    {
        $this->workers->whereIn('id', $workersId)
        ->update([
            'directrecruitment_status' => 'Not Arrived', 
            'modified_by' => $modifiedBy
        ]);
    }    

    /**
     * Returns a exported list of workers with their post arrival details.
     *
     * @param array $request The request data containing company id,  application_id, onboarding_country_id, user details with search filters
     * @return mixed The list of workers with their post arrival details.
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
            ->Join('worker_arrival', 'worker_arrival.worker_id', 'workers.id')
            ->Join('directrecruitment_arrival', 'directrecruitment_arrival.id', 'worker_arrival.arrival_id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request[self::REQUEST_COMPANY_ID])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == self::CUSTOMER) {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->where(function ($query) use ($request) {
                $query->where('worker_arrival.arrival_status', self::STATUS_NOT_ARRIVED)
                ->orWhere('worker_arrival.jtk_submitted_on', NULL);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchQuery($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'worker_arrival.jtk_submitted_on', 'worker_arrival.arrival_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }
}