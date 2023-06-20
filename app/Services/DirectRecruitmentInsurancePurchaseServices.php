<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerInsuranceAttachments;
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
     * @var workerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;

    /**
     * @var workerInsuranceAttachments
     */
    private WorkerInsuranceAttachments $workerInsuranceAttachments;

    /**
     * @var Storage
     */
    private Storage $storage;
    

    /**
     * DirectRecruitmentInsurancePurchaseServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param Workers $workers
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param WorkerInsuranceAttachments $workerInsuranceAttachments
     * @param Storage $storage;
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, Workers $workers, WorkerInsuranceDetails $workerInsuranceDetails, WorkerInsuranceAttachments $workerInsuranceAttachments, Storage $storage)
    {
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->workers                            = $workers;
        $this->workerInsuranceDetails             = $workerInsuranceDetails;
        $this->workerInsuranceAttachments         = $workerInsuranceAttachments;
        $this->storage = $storage;
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
                'agent_id' => 'required',
                'calling_visa_reference_number' => 'required',
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
        return $this->workers
        ->join('worker_visa', function ($join) {
            $join->on('worker_visa.worker_id', '=', 'workers.id')
            ->where('worker_visa.status', '=', 'Processed');
        })
        ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'worker_visa.worker_id')
        ->where([
            ['workers.application_id', $request['application_id']],
            ['workers.onboarding_country_id', $request['onboarding_country_id']],
            ['workers.agent_id', $request['agent_id']],
            ['workers.cancel_status', 0]
        ])
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('workers.name', 'like', '%'.$request['search'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'workers.application_id', 'workers.onboarding_country_id', 'workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'worker_insurance_details.insurance_status')->distinct('workers.id')
        ->distinct('workers.id')
        ->orderBy('workers.id','DESC')
        ->paginate(Config::get('services.paginate_row'));
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
        ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'workers.application_id', 'workers.onboarding_country_id', 'workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'worker_insurance_details.insurance_status')->distinct('workers.id')
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

            $visaProcessedCount = $this->workers
            ->join('worker_visa', function ($join) use ($request) {
                $join->on('worker_visa.worker_id', '=', 'workers.id')
                ->where([
                    ['worker_visa.status', '=', 'Processed'],
                    ['worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number']]
                ]);
            })
            ->where([
                ['workers.application_id', $request['application_id']],
                ['workers.onboarding_country_id', $request['onboarding_country_id']],
                ['workers.agent_id', $request['agent_id']],
                ['workers.cancel_status', 0]
            ])
            ->select('workers.id')
            ->distinct('workers.id')
            ->get()->toArray();

            $visaProcessedWorkersId = [];
            foreach ($visaProcessedCount as $visaProcessed){
                $visaProcessedWorkersId[] = $visaProcessed['id'];
            }

            if($visaProcessedWorkersId === array_intersect($visaProcessedWorkersId, $request['workers']) && $request['workers'] === array_intersect($request['workers'], $visaProcessedWorkersId)) {
                $visaReferenceNumberMatched = true;
            } else {
                $visaReferenceNumberMatched = false;
            }

            if(count($request['workers']) == count($visaProcessedCount) && $visaReferenceNumberMatched == true ) {

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

                foreach ($request['workers'] as $workerId) {

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
                    'agent_id' => $request['agent_id']
                ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['created_by']]);
                return true;
            }else{
                return [
                    'workerCountError' => true
                ];
            }
        }else{
            return false;
        }
        
    }
}