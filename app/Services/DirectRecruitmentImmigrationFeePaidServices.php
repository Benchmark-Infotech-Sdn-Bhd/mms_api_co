<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerImmigration;
use App\Models\WorkerImmigrationAttachments;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
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
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var WorkerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentExpensesServices
     */
    private DirectRecruitmentExpensesServices $directRecruitmentExpensesServices;
    

    /**
     * DirectRecruitmentImmigrationFeePaidServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param Workers $workers
     * @param WorkerImmigration $workerImmigration
     * @param WorkerImmigrationAttachments $workerImmigrationAttachments
     * @param WorkerVisa $workerVisa
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param Storage $storage;
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, Workers $workers, WorkerImmigration $workerImmigration, WorkerImmigrationAttachments $workerImmigrationAttachments, WorkerVisa $workerVisa, WorkerInsuranceDetails $workerInsuranceDetails, Storage $storage,DirectRecruitmentExpensesServices $directRecruitmentExpensesServices)
    {
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->workers                            = $workers;
        $this->workerImmigration                  = $workerImmigration;
        $this->workerImmigrationAttachments       = $workerImmigrationAttachments;
        $this->workerVisa                         = $workerVisa;
        $this->workerInsuranceDetails             = $workerInsuranceDetails;
        $this->storage                            = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
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

            $workers = explode(",", $request['workers']);

            if(is_array($workers)){

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

                foreach ($workers as $workerId) {

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
                    'onboarding_country_id' => $request['onboarding_country_id']
                ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['created_by']]);

                // ADD OTHER EXPENSES
                $request['expenses_application_id'] = $request['application_id'] ?? 0;
                $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[4];
                $request['expenses_payment_reference_number'] = $request['immigration_reference_number'] ?? '';
                $request['expenses_payment_date'] = $request['payment_date'] ?? '';
                $request['expenses_amount'] = $request['total_fee'] ?? 0;
                $request['expenses_remarks'] = $request['remarks'] ?? '';
                $this->directRecruitmentExpensesServices->addOtherExpenses($request);

                return true;

            } else{
                return  false;
            }

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
            ->where('worker_visa.approval_status', 'Approved')
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
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->where('worker_visa.approval_status', 'Approved')
            ->where('worker_immigration.immigration_status', null)
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.cancel_status' => 0
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->Where('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date', 'worker_immigration.immigration_status', DB::raw('COUNT(workers.id) as workers'), DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id'))
            ->selectRaw("(CASE WHEN (worker_immigration.immigration_status IS NULL) THEN 'Pending' ELSE worker_immigration.immigration_status END) as immigration_status_value")
            ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date', 'worker_immigration.immigration_status')
            ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        $processCallingVisa = $this->workerVisa
                            ->where('calling_visa_reference_number', $request['calling_visa_reference_number'])
                            ->first(['calling_visa_reference_number', 'submitted_on']);

        $insurancePurchase = $this->workerInsuranceDetails
                        ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_insurance_details.worker_id')
                        ->leftJoin('vendors', 'vendors.id', 'worker_insurance_details.insurance_provider_id')
                        ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
                        ->first(['worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date']);
                        
        $callingVisaApproval = $this->workerVisa
                        ->where('calling_visa_reference_number', $request['calling_visa_reference_number'])
                        ->first(['calling_visa_generated', 'calling_visa_valid_until']);
                        
        return [
            'process' => $processCallingVisa,
            'insurance' => $insurancePurchase,
            'approval' => $callingVisaApproval
        ];
    }
}