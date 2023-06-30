<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerVisa;
use App\Models\WorkerArrival;
use App\Models\DirectrecruitmentArrival;
use App\Models\WorkerFomema;
use App\Models\WorkerPLKSAttachments;
use App\Models\Workers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirectRecruitmentPostArrivalFomemaServices
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
     * @var WorkerPLKSAttachments
     */
    private WorkerPLKSAttachments $workerPLKSAttachments;
    /**
     * @var FOMEMAAttachment
     */
    private FOMEMAAttachment $fomemaAttachment;
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
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param WorkerVisa $workerVisa
     * @param WorkerArrival $workerArrival
     * @param DirectrecruitmentArrival $directrecruitmentArrival
     * @param WorkerFomema $workerFomema
     * @param WorkerPLKSAttachments $workerPLKSAttachments
     * @param Workers $workers
     * @param Storage $storage
     */
    public function __construct(DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, WorkerVisa $workerVisa, WorkerArrival $workerArrival, DirectrecruitmentArrival $directrecruitmentArrival, WorkerFomema $workerFomema, WorkerPLKSAttachments $workerPLKSAttachments, Workers $workers, Storage $storage)
    {
        $this->directRecruitmentPostArrivalStatus   = $directRecruitmentPostArrivalStatus;
        $this->workerVisa                           = $workerVisa;
        $this->workerArrival                        = $workerArrival;
        $this->directrecruitmentArrival             = $directrecruitmentArrival;
        $this->workerFomema                         = $workerFomema;
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
            ->where([
                'workers.application_id' => $request['application_id'],
                'workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_fomema.fomema_status' => 'Fit'
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'workers.application_id', 'workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'workers.fomema_valid_until', 'workers.special_pass_valid_until', 'workers.plks_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
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
            $this->workers->whereIn('worker_id', $request['workers'])
                ->update([
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
                        $this->fomemaAttachment->create([
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
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }
}