<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
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
     * @var DirectRecruitmentExpensesServices
     */
    private DirectRecruitmentExpensesServices $directRecruitmentExpensesServices;

    /**
     * DirectRecruitmentPostArrivalFomemaServices constructor.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param WorkerFomema $workerFomema
     * @param FOMEMAAttachment $fomemaAttachment
     * @param Workers $workers
     * @param Storage $storage
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices
     */
    public function __construct(DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, WorkerFomema $workerFomema, FOMEMAAttachment $fomemaAttachment, Workers $workers, Storage $storage, DirectRecruitmentExpensesServices $directRecruitmentExpensesServices)
    {
        $this->directRecruitmentPostArrivalStatus   = $directRecruitmentPostArrivalStatus;
        $this->workerFomema                         = $workerFomema;
        $this->fomemaAttachment                     = $fomemaAttachment;
        $this->workers                              = $workers;
        $this->storage                              = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
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
            ->leftJoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_arrival.arrival_status' => 'Arrived',
                'worker_fomema.fomema_status' => 'Pending'
            ])
            ->whereNotNull('worker_arrival.jtk_submitted_on')
            ->whereIn('workers.special_pass', [0,2])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.special_pass_valid_until', 'worker_fomema.purchase_date', 'worker_fomema.fomema_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
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
        
        // ADD OTHER EXPENSES - Onboarding - FOMEMA Total Charge + Convenient Fee (RM)
        $request['expenses_application_id'] = $request['application_id'] ?? 0;
        $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[6];
        $request['expenses_payment_reference_number'] = '';
        $request['expenses_payment_date'] = $request['purchase_date'];
        $request['expenses'] = $request['fomema_total_charge'] + ($request['convenient_fee'] ?? 3);
        $request['expenses_amount'] = $request['expenses'] ?? 0;
        $request['expenses_remarks'] = $request['remarks'] ?? '';
        $this->directRecruitmentExpensesServices->addOtherExpenses($request);
        
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
                    'clinic_name' => $request['clinic_name'], 
                    'doctor_code' => $request['doctor_code'], 
                    'allocated_xray' => $request['allocated_xray'], 
                    'xray_code' => $request['xray_code'],
                    'fomema_status' => 'Fit',
                    'modified_by' => $request['modified_by']
                ]);
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'directrecruitment_status' => 'FOMEMA Fit',
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
    /**
     * @param $request
     * @return array|bool
     */
    public function updateSpecialPass($request): array|bool
    {
        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'special_pass' => 1, 
                    'modified_by' => $request['modified_by']
                ]);
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }
    /**
     * @param $request
     * @return mixed
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
            ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_arrival.arrival_status' => 'Arrived',
                'worker_fomema.fomema_status' => 'Pending'
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.special_pass_valid_until', 'worker_fomema.purchase_date', 'worker_fomema.fomema_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }
}