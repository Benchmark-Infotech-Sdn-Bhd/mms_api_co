<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerInsuranceAttachments;
use App\Models\Vendor;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DirectRecruitmentInsurancePurchaseServices
{
    /**
     * @var directRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;

    /**
     * @var workers
     */
    private Workers $workers;

    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;

    /**
     * @var workerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;

    /**
     * @var workerInsuranceAttachments
     */
    private WorkerInsuranceAttachments $workerInsuranceAttachments;
    /**
     * @var Vendor
     */
    private Vendor $vendor;

    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentExpensesServices
     */
    private DirectRecruitmentExpensesServices $directRecruitmentExpensesServices;
    

    /**
     * DirectRecruitmentInsurancePurchaseServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param WorkerInsuranceAttachments $workerInsuranceAttachments
     * @param Vendor $vendor
     * @param Storage $storage;
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, Workers $workers, WorkerVisa $workerVisa, WorkerInsuranceDetails $workerInsuranceDetails, WorkerInsuranceAttachments $workerInsuranceAttachments, Vendor $vendor, Storage $storage, DirectRecruitmentExpensesServices $directRecruitmentExpensesServices)
    {
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->workers                            = $workers;
        $this->workerVisa                         = $workerVisa;
        $this->workerInsuranceDetails             = $workerInsuranceDetails;
        $this->workerInsuranceAttachments         = $workerInsuranceAttachments;
        $this->vendor                             = $vendor;
        $this->storage = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
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
                'ig_policy_number' => 'required', 
                'hospitalization_policy_number' => 'required', 
                'insurance_provider_id' => 'required',
                'ig_amount' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
                'hospitalization_amount' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
                'insurance_submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow',
                'insurance_expiry_date' => 'required|date|date_format:Y-m-d'
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
        ->join('worker_visa', function ($join) {
            $join->on('worker_visa.worker_id', '=', 'workers.id')
            ->where('worker_visa.status', '=', 'Processed');
        })
        ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'worker_visa.worker_id')
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
        ->where('worker_insurance_details.insurance_status', 'Pending')
        ->where([
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
            ['workers.cancel_status', 0]
        ])
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('workers.name', 'like', '%'.$request['search'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
            }
        });
        if(isset($request['export']) && !empty($request['export']) ){
            $data = $data->select('workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on','worker_insurance_details.insurance_status')->distinct('workers.id')
            ->distinct('workers.id')
            ->orderBy('workers.id','DESC')
            ->get();
        }else{
            $data = $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'worker_insurance_details.insurance_status')->distinct('workers.id')
            ->distinct('workers.id')
            ->orderBy('workers.id','DESC')
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
        return $this->workers
        ->join('worker_visa', function ($join) {
            $join->on('worker_visa.worker_id', '=', 'workers.id')
            ->where('worker_visa.status', '=', 'Processed');
        })
        ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'worker_visa.worker_id')
        ->where([
            ['workers.id', $request['id']]
        ])
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
        ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'worker_insurance_details.insurance_status')->distinct('workers.id')
        ->with(['workerInsuranceAttachments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->distinct('workers.id')
        ->get();
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function submit($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->submitValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {

            $workers = explode(",", $request['workers']);

            if(is_array($workers)){

                $workerVisaProcessed = $this->workerVisa
            ->whereIn('worker_id', $workers)
            ->where('status', 'Processed')
            ->select('calling_visa_reference_number')
            ->groupBy('calling_visa_reference_number')
            ->get()->toArray();

            if(count($workerVisaProcessed) == 1){
                $callingVisaReferenceNumberCount = $this->workerVisa->where([
                    'status' => 'Processed',
                    'calling_visa_reference_number' => $workerVisaProcessed[0]['calling_visa_reference_number'] ?? ''
                    ])->count('worker_id');

                if( count($workers) <> $callingVisaReferenceNumberCount ){
                    return [
                        'workerCountError' => true
                    ];
                }  
            }else{
                return [
                    'visaReferenceNumberCountError' => true
                ];
            }

            if (request()->hasFile('attachment')){
                foreach($request->file('attachment') as $file){
                    $fileName = $file->getClientOriginalName();
                    $filePath = '/directRecruitment/onboarding/insurancePurchase/' . $fileName; 
                    $linode = $this->storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = $this->storage::disk('linode')->url($filePath);  
                }
            }else{
                $fileName = '';
                $fileUrl = '';
            }

            foreach ($workers as $workerId) {

                $this->workerInsuranceDetails->updateOrCreate(
                    ['worker_id' => $workerId],
                    ['ig_policy_number' => $request['ig_policy_number'], 
                    'hospitalization_policy_number' => $request['hospitalization_policy_number'], 
                    'insurance_provider_id' => $request['insurance_provider_id'],
                    'ig_amount' => $request['ig_amount'],
                    'hospitalization_amount' => $request['hospitalization_amount'],
                    'insurance_submitted_on' => $request['insurance_submitted_on'],
                    'insurance_expiry_date' => $request['insurance_expiry_date'],
                    'insurance_status' => 'Purchased', 
                    'created_by' => $params['created_by'],
                    'modified_by' => $params['created_by']
                ]);

                if(!empty($fileName) && !empty($fileUrl)){
                    $this->workerInsuranceAttachments->updateOrCreate(
                        ['file_id' => $workerId],
                        ["file_name" => $fileName,
                        "file_type" => 'Insurance Purchase',
                        "file_url" =>  $fileUrl
                    ]);
                }

            }
            $this->directRecruitmentCallingVisaStatus->where([
                'application_id' => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id'],
            ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['created_by']]);

            // ADD OTHER EXPENSES - Onboarding - Calling Visa - I.G Insurance
            $request['expenses_application_id'] = $request['application_id'] ?? 0;
            $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[3];
            $request['expenses_payment_reference_number'] = '';
            $request['expenses_payment_date'] = $request['insurance_submitted_on'];
            $request['expenses_amount'] = $request['ig_amount'] ?? 0;
            $request['expenses_remarks'] = $request['remarks'] ?? '';
            $this->directRecruitmentExpensesServices->addOtherExpenses($request);

            // ADD OTHER EXPENSES - Onboarding - Calling Visa - Hospitalisation
            $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[5];
            $request['expenses_payment_date'] = $request['insurance_submitted_on'];
            $request['expenses_amount'] = $request['hospitalization_amount'] ?? 0;
            $this->directRecruitmentExpensesServices->addOtherExpenses($request);

            return true;

            }else{
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
    public function insuranceProviderDropDown($request): mixed
    {
        return $this->vendor
        ->where('type', 'Insurance')
        ->select('id', 'name')
        ->distinct('id')
        ->get();
    }
}