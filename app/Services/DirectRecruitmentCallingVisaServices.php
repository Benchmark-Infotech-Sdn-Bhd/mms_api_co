<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\CancellationAttachment;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
     * DirectRecruitmentCallingVisaServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param CancellationAttachment $cancellationAttachment
     * @param Storage $storage
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, Workers $workers, WorkerVisa $workerVisa, CancellationAttachment $cancellationAttachment, Storage $storage)
    {
        $this->directRecruitmentCallingVisaStatus   = $directRecruitmentCallingVisaStatus;
        $this->workers                              = $workers;
        $this->workerVisa                           = $workerVisa;
        $this->cancellationAttachment               = $cancellationAttachment;
        $this->storage                              = $storage;
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
            ->select('id', 'item', 'updated_on', 'status')
            ->where([
                'application_id' => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->orderBy('id', 'desc')
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
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_visa.status' => 'Pending'
            ])
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
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
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
            ->select('id', 'name', 'passport_number', 'application_id', 'onboarding_country_id', 'agent_id')
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
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id']
            ])
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
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'workers.cancel_status', 'workers.date_of_birth', 'workers.gender')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
}