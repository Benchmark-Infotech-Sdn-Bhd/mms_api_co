<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerVisa;
use App\Models\WorkerArrival;
use App\Models\DirectrecruitmentArrival;
use App\Models\WorkerFomema;
use App\Models\FOMEMAAttachment;
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
     * @var WorkerFomema
     */
    private WorkerFomema $workerFomema;
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
     * @param FOMEMAAttachment $fomemaAttachment
     * @param Workers $workers
     * @param Storage $storage
     */
    public function __construct(DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, WorkerVisa $workerVisa, WorkerArrival $workerArrival, DirectrecruitmentArrival $directrecruitmentArrival, WorkerFomema $workerFomema, FOMEMAAttachment $fomemaAttachment, Workers $workers, Storage $storage)
    {
        $this->directRecruitmentPostArrivalStatus   = $directRecruitmentPostArrivalStatus;
        $this->workerVisa                           = $workerVisa;
        $this->workerArrival                        = $workerArrival;
        $this->directrecruitmentArrival             = $directrecruitmentArrival;
        $this->workerFomema                         = $workerFomema;
        $this->fomemaAttachment                     = $fomemaAttachment;
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
    public function purchaseValidation(): array
    {
        return [
            'purchase_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'fomema_total_charge' => 'required|regex:/^(([0-9]{0,3}+)(\.([0-9]{0,2}+))?)$/'
        ];
    }
    /**
     * @return array
     */
    public function fomemaFitValidation(): array
    {
        return [
            'clinic_name' => 'required|regex:/^[a-zA-Z ]*$/',
            'doctor_code' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'allocated_xray' => 'required|regex:/^[a-zA-Z ]*$/',
            'xray_code' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'fomema_valid_until' => 'required|date|date_format:Y-m-d|after:yesterday',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * @return array
     */
    public function fomemaUnfitValidation(): array
    {
        return [
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'

        ];
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
            ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->where([
                'workers.application_id' => $request['application_id'],
                'workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_arrival.arrival_status' => 'Arrived'
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'workers.application_id', 'workers.onboarding_country_id', 'workers.special_pass_valid_until', 'worker_fomema.purchase_date', 'worker_fomema.fomema_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function purchase($request): array|bool
    {
        $validator = Validator::make($request, $this->purchaseValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->workerFomema->whereIn('worker_id', $request['workers'])
                ->update([
                    'purchase_date' => $request['purchase_date'], 
                    'fomema_total_charge' => $request['fomema_total_charge'], 
                    'convenient_fee' => $request['convenient_fee'] ?? 3, 
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
    public function fomemaFit($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->fomemaFitValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);
            $this->workerFomema->whereIn('worker_id', $request['workers'])
                ->update([
                    'clinic_name' => $request['purchase_date'], 
                    'doctor_code' => $request['doctor_code'], 
                    'allocated_xray' => $request['allocated_xray'], 
                    'xray_code' => $request['xray_code'],
                    'fomema_status' => 'Fit',
                    'modified_by' => $request['modified_by']
                ]);
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'fomema_valid_until' => $request['fomema_valid_until'], 
                    'modified_by' => $request['modified_by']
                ]);
            if(request()->hasFile('attachment')) {
                foreach ($request['workers'] as $workerId) {
                    foreach($request->file('attachment') as $file) {
                        $fileName = $file->getClientOriginalName();
                        $filePath = 'directRecruitment/workers/fomema/' . $workerId. '/'. $fileName; 
                        $linode = $this->storage::disk('linode');
                        $linode->put($filePath, file_get_contents($file));
                        $fileUrl = $this->storage::disk('linode')->url($filePath);
                        $this->fomemaAttachment->create([
                            'file_id' => $workerId,
                            'file_name' => $fileName,
                            'file_type' => 'FOMEMA Fit',
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
    /**
     * @param $request
     * @return array|bool
     */
    public function fomemaUnfit($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->fomemaUnfitValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);
            $this->workerFomema->whereIn('worker_id', $request['workers'])
                ->update([
                    'fomema_status' => 'Unfit', 
                    'modified_by' => $request['modified_by']
                ]);
            if(request()->hasFile('attachment')) {
                foreach ($request['workers'] as $workerId) {
                    foreach($request->file('attachment') as $file) {
                        $fileName = $file->getClientOriginalName();
                        $filePath = 'directRecruitment/workers/fomema/' . $workerId. '/'. $fileName; 
                        $linode = $this->storage::disk('linode');
                        $linode->put($filePath, file_get_contents($file));
                        $fileUrl = $this->storage::disk('linode')->url($filePath);
                        $this->fomemaAttachment->create([
                            'file_id' => $workerId,
                            'file_name' => $fileName,
                            'file_type' => 'FOMEMA Unfit Letter',
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