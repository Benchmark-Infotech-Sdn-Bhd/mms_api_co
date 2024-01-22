<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\OnboardingAttestation;
use App\Models\OnboardingDispatch;
use App\Models\OnboardingEmbassy;
use App\Models\EmbassyAttestationFileCosting;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use App\Models\User;

class DirectRecruitmentOnboardingAttestationServices
{

    public const REQUEST_COMPANY_ID = 'company_id';
    public const REQUEST_APPLICATION_ID = 'application_id';
    public const REQUEST_ONBOARDING_COUNTRY_ID = 'onboarding_country_id';
    public const REQUEST_ONBOARDING_AGENT_ID = 'onboarding_agent_id';
    public const REQUEST_KSM_REFERENCE_NUMBER = 'ksm_reference_number';
    public const REQUEST_ONBOARDING_ATTESTATION_ID = 'onboarding_attestation_id';

    public const REQUEST_ITEM_NAME = 'Attestation Submission';
    public const REQUEST_ATTESTATION_STATUS = 'Pending';

    public const ONBOARDING_STATUS = 2;
    public const DEFAULT_INT_VALUE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_IN_ACTIVE = 0;

    /**
     * @var OnboardingAttestation
     */
    private OnboardingAttestation $onboardingAttestation;

    /**
     * @var OnboardingDispatch
     */
    private OnboardingDispatch $onboardingDispatch;

    /**
     * @var OnboardingEmbassy
     */
    private OnboardingEmbassy $onboardingEmbassy;

    /**
     * @var EmbassyAttestationFileCosting
     */
    private EmbassyAttestationFileCosting $embassyAttestationFileCosting;

    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;

    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentExpensesServices
     */
    private DirectRecruitmentExpensesServices $directRecruitmentExpensesServices;

    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var NotificationServices
     */
    private NotificationServices $notificationServices;

    /**
     * DirectRecruitmentOnboardingAttestationServices constructor method.
     * 
     * @param OnboardingAttestation $onboardingAttestation The onBoarding Attestation Object;
     * @param OnboardingDispatch $onboardingDispatch The onBoarding Dispatch Object;
     * @param OnboardingEmbassy $onboardingEmbassy The onBoarding Embassy Object;
     * @param EmbassyAttestationFileCosting $embassyAttestationFileCosting The Embassy Attestation File Costing Object;
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices The Direct Recruitment OnBoarding Country Services;
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The Direct Recruitment OnBoarding Country Object;
     * @param Storage $storage The storage ;
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices The Direct Recruitment Expenses services
     * @param NotificationServices $notificationServices The Notification services
     */

    public function __construct(
        OnboardingAttestation $onboardingAttestation, 
        OnboardingDispatch $onboardingDispatch, 
        OnboardingEmbassy $onboardingEmbassy, 
        EmbassyAttestationFileCosting $embassyAttestationFileCosting, 
        DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices, 
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, 
        Storage $storage, 
        DirectRecruitmentExpensesServices $directRecruitmentExpensesServices, 
        NotificationServices $notificationServices
    )
    {
        $this->onboardingAttestation = $onboardingAttestation;
        $this->onboardingDispatch = $onboardingDispatch;
        $this->onboardingEmbassy = $onboardingEmbassy;
        $this->embassyAttestationFileCosting = $embassyAttestationFileCosting;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->storage = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->notificationServices = $notificationServices;
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function createValidation(): array
    {
        return [
            'application_id' => 'required',
            'onboarding_country_id' => 'required',
            'ksm_reference_number' => 'required'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function updateDispatchValidation(): array
    {
        return [
            'onboarding_attestation_id' => 'required',
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required',
            'reference_number' => 'required',
            'employee_id' => 'required',
            'from' => 'required',
            'calltime' => 'required|date|date_format:Y-m-d',
            'area' => 'required',
            'employer_name' => 'required',
            'phone_number' => 'required'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function uploadEmbassyFileValidation(): array
    {
        return [
            'onboarding_attestation_id' => 'required',
            'embassy_attestation_id' => 'required',
        ];
    }
    /**
     * Returns a paginated list of on Boarding Attestation details with direct recruitment application details.
     *
     * @param array $request The request data containing company id,  application_id, onboarding_country_id
     * @return mixed \Illuminate\Pagination\LengthAwarePaginator The paginated list of direct recruitment onboarding agent with direct recruitment application details.
     */ 
    public function list($request): mixed
    {
        return $this->onboardingAttestation
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })
        ->where([
            ['onboarding_attestation.application_id', $request[self::REQUEST_APPLICATION_ID]],
            ['onboarding_attestation.onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID]],
        ])
        ->select('onboarding_attestation.id', 'onboarding_attestation.application_id', 'onboarding_attestation.onboarding_country_id', 'onboarding_attestation.onboarding_agent_id', 'onboarding_attestation.ksm_reference_number', 'onboarding_attestation.item_name', 'onboarding_attestation.status', 'onboarding_attestation.submission_date', 'onboarding_attestation.collection_date', 'onboarding_attestation.created_at', 'onboarding_attestation.updated_at')
        ->orderBy('onboarding_attestation.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * Show the Boarding Attestation details.
     *
     * @param array $request The request data containing id, company id
     * @return mixed Returns details of  direct recruitment on boarding Attestation.
     */
    public function show($request): mixed
    {
        return $this->onboardingAttestation->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->where('onboarding_attestation.id', $request['id'])
            ->first('onboarding_attestation.*');
    }

    /**
     * Get the onBoarding Attestation details for the given input details
     * 
     * - application_id: Direct Recruitment Application Id
     * - onboarding_country_id: Direct Recruitment Onboarding Country Id.
     * - onboarding_agent_id: on Boarding Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     *
     * @param array $request The data used to get the Direct Recruitment Attestation details.
     * Return mixed the Attestation object
     */
    public function checkOnboardingAttestationData(mixed $request): mixed
    {
        return $this->onboardingAttestation->where([
            ['application_id', $request[self::REQUEST_APPLICATION_ID]],
            ['onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID]],
            ['onboarding_agent_id', $request[self::REQUEST_ONBOARDING_AGENT_ID]],
            ['ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER]]
        ])->first(['id', 'application_id', 'onboarding_country_id']);
    }

    /**
     * Create a new Direct Recruitment onboarding Attestation .
     *
     * @param array $inputData The data used to create the Direct Recruitment Onboarding Attestation.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: OnBoarding Country Id.
     * - onboarding_agent_id: On Boarding Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     * - item_name: Item Name.
     * - status: Direct Recruitment Onboarding Attestation status .
     * - created_by: The user who created the Direct Recruitment Onboarding Attestation.
     * - modified_by: The user who modified the Direct Recruitment Onboarding Attestation.
     *
     * @return mixed The newly created Direct Recruitment Onboarding Attestation.
     */
    public function createDirectRecruitmentOnboardingAttestation(array $request):void
    {
        $this->onboardingAttestation->create([
            'application_id' => $request[self::REQUEST_APPLICATION_ID] ?? 0,
            'onboarding_country_id' => $request[self::REQUEST_ONBOARDING_COUNTRY_ID] ?? 0,
            'onboarding_agent_id' => $request[self::REQUEST_ONBOARDING_AGENT_ID] ?? 0,
            'ksm_reference_number' => $request[self::REQUEST_KSM_REFERENCE_NUMBER] ?? '',
            'item_name' => self::REQUEST_ITEM_NAME,
            'status' => self::REQUEST_ATTESTATION_STATUS,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
    }

    /**
     * Create a new Direct Recruitment Onboarding Attestation .
     *
     * @param array $inputData The data used to create the Direct Recruitment Onboarding Attestation.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: OnBoarding Country Id.
     * - onboarding_agent_id: on Boarding Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     * - status: Direct Recruitment Onboarding Attestation status .
     * - created_by: The user who created the Direct Recruitment Onboarding Attestation.
     * - modified_by: The user who modified the Direct Recruitment Onboarding Attestation.
     *
     * @return mixed The newly created Direct Recruitment Onboarding Attestation.
     */  
    public function create($request): bool|array
    {
        $onboardingAttestation = $this->checkOnboardingAttestationData($request);

        if(is_null($onboardingAttestation)){
            $validator = Validator::make($request, $this->createValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
            
            $this->createDirectRecruitmentOnboardingAttestation($request);

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
        $onboardingAttestation = $this->onboardingAttestation
            ->join('directrecruitment_applications', function ($join) use($request) {
                $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->where('directrecruitment_applications.company_id', $request['company_id']);
            })->select('onboarding_attestation.*')->find($request['id']);
        if(is_null($onboardingAttestation)) {
            return[
                'InvalidUser' => true
            ];
        }
        if (isset($request['submission_date']) && !empty($request['submission_date'])) {
            $request['status'] = 'Submitted';
        }
        if (isset($request['collection_date']) && !empty($request['collection_date'])) {
            $request['status'] = 'Collected';
        }
        if(isset($request['submission_date']) && !empty($request['submission_date'])){
            $onboardingAttestation->submission_date =  $request['submission_date'];
        }
        if(isset($request['collection_date']) && !empty($request['collection_date'])){
            $onboardingAttestation->collection_date =  $request['collection_date'];

            $attestationCount = $this->onboardingAttestation->where('application_id', $onboardingAttestation->application_id)
                                    ->where('onboarding_country_id', $onboardingAttestation->onboarding_country_id)
                                    ->where('status', 'Collected')
                                    ->count();
            if($attestationCount == 0) {
                $onBoardingStatus['application_id'] = $onboardingAttestation->application_id;
                $onBoardingStatus['country_id'] = $onboardingAttestation->onboarding_country_id;
                $onBoardingStatus['onboarding_status'] = 3; //Agent Added
                $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);
            }
        }
        $onboardingAttestation->file_url =  $request['file_url'] ?? $onboardingAttestation->file_url;
        $onboardingAttestation->remarks =  $request['remarks'] ?? $onboardingAttestation->remarks;
        $onboardingAttestation->status =  $request['status'] ?? $onboardingAttestation->status;
        $onboardingAttestation->modified_by =  $request['modified_by'] ?? $onboardingAttestation->modified_by;
        $onboardingAttestation->save();
        return true;
    }
    /**
     * Show the direct recruitment on boarding agent details.
     *
     * @param array $request The request data containing company_id, onboarding_attestation_id
     * @return mixed Returns details of direct recruitment on-boarding agent details.
     */
    public function showDispatch($request): mixed
    {
        return $this->onboardingDispatch
        ->join('onboarding_attestation', 'onboarding_attestation.id', 'onboarding_dispatch.onboarding_attestation_id')
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
            ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })->where('onboarding_dispatch.onboarding_attestation_id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID])
        ->select('onboarding_dispatch.*')
        ->get();
    }
    /**
     * update dispatch
     * @param $request
     * @return bool|array
     */
    public function updateDispatch($request): bool|array
    {
        $attestationCheck = $this->onboardingAttestation->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->where('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('onboarding_attestation.id', $request['onboarding_attestation_id'])
            ->first('onboarding_attestation.*');
            
        if(is_null($attestationCheck)) {
            return [
                'InvalidUser' => true
            ];
        }
        $onboardingDispatch = $this->onboardingDispatch->where(
            'onboarding_attestation_id', $request['onboarding_attestation_id']
        )->first(['id', 'onboarding_attestation_id', 'date', 'time', 'reference_number', 'employee_id', 'from', 'calltime', 'area', 'employer_name', 'phone_number', 'remarks']);

        $request['reference_number'] = 'JO00000'.$this->onboardingDispatch->count() + 1;

        $validator = Validator::make($request, $this->updateDispatchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        
        if(is_null($onboardingDispatch)){
            $this->onboardingDispatch->create([
                'onboarding_attestation_id' => $request['onboarding_attestation_id'] ?? 0,
                'date' => $request['date'] ?? null,
                'time' => $request['time'] ?? '',
                'reference_number' => $request['reference_number'],
                'employee_id' => $request['employee_id'] ?? '',
                'from' => $request['from'] ?? '',
                'calltime' => $request['calltime'] ?? null,
                'area' => $request['area'] ?? '',
                'employer_name' => $request['employer_name'] ?? '',
                'phone_number' => $request['phone_number'] ?? '',
                'remarks' => $request['remarks'] ?? '',
                'created_by' =>  $request['created_by'] ?? 0,
                'modified_by' =>  $request['created_by'] ?? 0
            ]);
        }else{
            $onboardingDispatch->update([
                'date' => $request['date'] ?? null,
                'time' => $request['time'] ?? '',
                'employee_id' => $request['employee_id'] ?? '',
                'from' => $request['from'] ?? '',
                'calltime' => $request['calltime'] ?? null,
                'area' => $request['area'] ?? '',
                'employer_name' => $request['employer_name'] ?? '',
                'phone_number' => $request['phone_number'] ?? '',
                'remarks' => $request['remarks'] ?? '',
                'modified_by' =>  $request['created_by'] ?? 0
            ]);
        }

        $getUser = $this->getUser($request['employee_id']);
        if($getUser){
            $NotificationParams['user_id'] = $request['employee_id'];
            $NotificationParams['from_user_id'] = $request['created_by'];
            $NotificationParams['type'] = 'Dispatches';
            $NotificationParams['title'] = 'Dispatches';
            $NotificationParams['message'] = $request['reference_number'].' Dispatch is Assigned';
            $NotificationParams['status'] = 1;
            $NotificationParams['read_flag'] = 0;
            $NotificationParams['created_by'] = $request['created_by'];
            $NotificationParams['modified_by'] = $request['created_by'];
            $NotificationParams['company_id'] = $request['company_id'];
            $this->notificationServices->insertDispatchNotification($NotificationParams);
            dispatch(new \App\Jobs\RunnerNotificationMail($getUser,$NotificationParams['message']));
        }
        
        return true;
    }
    /**
     * list embassy file
     * @param $request
     * @return mixed
     */   
    public function listEmbassy($request): mixed
    {
        $onboardingAttestation = $this->onboardingAttestation
        ->leftJoin('directrecruitment_onboarding_countries', 'directrecruitment_onboarding_countries.id', 'onboarding_attestation.onboarding_country_id')
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
        })
        ->where('onboarding_attestation.id', $request['onboarding_attestation_id'])
        ->select('directrecruitment_onboarding_countries.country_id')
        ->distinct('directrecruitment_onboarding_countries.country_id')
        ->get();
        if(count($onboardingAttestation) == 0) {
            return [
                'InvalidUser' => true
            ];
        }
        $request['country_id'] = $onboardingAttestation[0]['country_id'] ?? 0;

        return $this->embassyAttestationFileCosting
        ->leftJoin('onboarding_embassy', function ($join) use ($request) {
            $join->on('onboarding_embassy.embassy_attestation_id', '=', 'embassy_attestation_file_costing.id')
            ->where([
                ['onboarding_embassy.onboarding_attestation_id', $request['onboarding_attestation_id']],
                ['onboarding_embassy.deleted_at', null],
            ]);
        })
        ->where([
            ['embassy_attestation_file_costing.country_id', $request['country_id']]
        ])
        ->select('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id', 'embassy_attestation_file_costing.title', 'embassy_attestation_file_costing.amount', 'onboarding_embassy.id as onboarding_embassy_id', 'onboarding_embassy.file_name as onboarding_embassy_file_name', 'onboarding_embassy.file_url as onboarding_embassy_file_url', 'onboarding_embassy.amount as onboarding_embassy_amount')
        ->distinct('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id')
        ->orderBy('embassy_attestation_file_costing.id','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * show embassy file
     * @param $request
     * @return mixed
     */   
    public function showEmbassyFile($request): mixed
    {
        $attestationCheck = $this->onboardingAttestation->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('onboarding_attestation.id', $request['onboarding_attestation_id'])
            ->first('onboarding_attestation.*');

        if(is_null($attestationCheck)) {
            return [
                'InvalidUser' => true
            ];
        }
        return $this->embassyAttestationFileCosting
        ->leftJoin('onboarding_embassy', function ($join) use ($request) {
            $join->on('onboarding_embassy.embassy_attestation_id', '=', 'embassy_attestation_file_costing.id')
            ->where([
                ['onboarding_embassy.onboarding_attestation_id', $request['onboarding_attestation_id']],
                ['onboarding_embassy.deleted_at', null],
            ]);
        })
        ->where([
            ['embassy_attestation_file_costing.id', $request['embassy_attestation_id']]
        ])
        ->select('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id', 'embassy_attestation_file_costing.title', 'embassy_attestation_file_costing.amount', 'onboarding_embassy.id as onboarding_embassy_id', 'onboarding_embassy.file_name as onboarding_embassy_file_name', 'onboarding_embassy.file_url as onboarding_embassy_file_url', 'onboarding_embassy.amount as onboarding_embassy_amount')
        ->distinct('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id')
        ->first();
    }

    /**
     * upload embassy file 
     * @param $request
     * @return bool|array
     */
    public function uploadEmbassyFile($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $validator = Validator::make($request->toArray(), $this->uploadEmbassyFileValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $onboardingAttestation = $this->onboardingAttestation
        ->join('directrecruitment_applications', function ($join) use($params) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->where('directrecruitment_applications.company_id', $params['company_id']);
        })
        ->where('onboarding_attestation.id', $params['onboarding_attestation_id'])
        ->first(['onboarding_attestation.application_id']);

        if(is_null($onboardingAttestation)) {
            return [
                'InvalidUser' => true
            ];
        }
        $request['application_id'] = isset($onboardingAttestation['application_id']) ? $onboardingAttestation['application_id'] : 0;

        $onboardingEmbassy = $this->onboardingEmbassy->where([
            ['onboarding_attestation_id', $request['onboarding_attestation_id']],
            ['embassy_attestation_id', $request['embassy_attestation_id']],
            ['deleted_at', null],
        ])->first(['id', 'onboarding_attestation_id', 'embassy_attestation_id', 'file_name', 'file_type', 'file_url', 'amount']);
        
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/onboarding/embassyAttestationCosting/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                if(is_null($onboardingEmbassy)){
                    $this->onboardingEmbassy::create([
                        "onboarding_attestation_id" => $request['onboarding_attestation_id'] ?? 0,
                        "embassy_attestation_id" => $request['embassy_attestation_id'] ?? 0,
                        "file_name" => $fileName,
                        "file_type" => 'Embassy Attestation Costing',
                        "file_url" =>  $fileUrl,
                        "amount" => $request['amount'] ?? 0,
                        "created_by" =>  $params['created_by'] ?? 0,
                        "modified_by" =>  $params['created_by'] ?? 0
                    ]); 
                }else{
                    $onboardingEmbassy->update([
                        "file_name" => $fileName,
                        "file_url" =>  $fileUrl,
                        "amount" => $request['amount'] ?? $onboardingEmbassy->amount,
                        "modified_by" =>  $params['created_by'] ?? 0
                    ]); 
                }
                // ADD OTHER EXPENSES - Onboarding - Attestation Costing
                $request['expenses_application_id'] = $request['application_id'] ?? 0;
                $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[2];
                $request['expenses_payment_reference_number'] = '';
                $request['expenses_payment_date'] = Carbon::now();
                $request['expenses_amount'] = $request['amount'] ?? 0;
                $request['expenses_remarks'] = '';
                $this->directRecruitmentExpensesServices->addOtherExpenses($request);

            }
        }elseif( isset($request['amount'])){
            if(is_null($onboardingEmbassy)){
                $this->onboardingEmbassy::create([
                    "onboarding_attestation_id" => $request['onboarding_attestation_id'] ?? 0,
                    "embassy_attestation_id" => $request['embassy_attestation_id'] ?? 0,
                    "amount" => $request['amount'] ?? 0,
                    "created_by" =>  $params['created_by'] ?? 0,
                    "modified_by" =>  $params['created_by'] ?? 0
                ]); 
            }else{
                $onboardingEmbassy->update([
                    "amount" => $request['amount'],
                    "modified_by" =>  $params['created_by'] ?? 0
                ]); 
            }
            // ADD OTHER EXPENSES - Onboarding - Attestation Costing
            $request['expenses_application_id'] = $request['application_id'] ?? 0;
            $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[2];
            $request['expenses_payment_reference_number'] = '';
            $request['expenses_payment_date'] = Carbon::now();
            $request['expenses_amount'] = $request['amount'] ?? 0;
            $request['expenses_remarks'] = '';
            $this->directRecruitmentExpensesServices->addOtherExpenses($request);
        }else{
            return false;
        }
        return true;
    }


    /**
     * delete embassy file
     * @param $request
     * @return array
     */    
    public function deleteEmbassyFile($request): array
    {   
        $data = $this->onboardingEmbassy
        ->join('onboarding_attestation', 'onboarding_attestation.id', 'onboarding_embassy.onboarding_attestation_id')
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
            ->where('directrecruitment_applications.company_id', $request['company_id']);
        })->find($request['onboarding_embassy_id']); 
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * * get user
     * @param $request
     */    
    public function getUser($referenceId)
    {   
        return User::where('reference_id',$referenceId)->where('user_type','Employee')->first('id', 'name', 'email');
    }
    /**
     * @param $request
     * @return bool
     */    
    public function updateKSMReferenceNumber($request): bool
    {   
        return $this->onboardingAttestation->where('application_id', $request['application_id'])
                ->where('onboarding_country_id', $request['onboarding_country_id'])
                ->where('onboarding_agent_id', $request['id'])
                ->where('ksm_reference_number', $request['old_ksm_reference_number'])
                ->update(['ksm_reference_number' => $request['ksm_reference_number']]); 
    }

}