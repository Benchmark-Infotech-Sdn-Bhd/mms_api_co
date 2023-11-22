<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerRepatriation;
use App\Models\WorkerRepatriationAttachments;
use App\Models\Workers;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirectRecruitmentRepatriationServices
{
    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var WorkerRepatriation
     */
    private WorkerRepatriation $workerRepatriation;
    /**
     * @var WorkerRepatriationAttachments
     */
    private WorkerRepatriationAttachments $workerRepatriationAttachments;
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
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;

    /**
     * DirectRecruitmentRepatriationServices constructor.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param WorkerRepatriation $workerRepatriation
     * @param WorkerRepatriationAttachments $workerRepatriationAttachments
     * @param Workers $workers
     * @param Storage $storage
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices
     */
    public function __construct(DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, WorkerRepatriation $workerRepatriation, WorkerRepatriationAttachments $workerRepatriationAttachments, Workers $workers, Storage $storage, DirectRecruitmentExpensesServices $directRecruitmentExpensesServices, DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices)
    {
        $this->directRecruitmentPostArrivalStatus           = $directRecruitmentPostArrivalStatus;
        $this->workerRepatriation                           = $workerRepatriation;
        $this->workerRepatriationAttachments                = $workerRepatriationAttachments;
        $this->workers                                      = $workers;
        $this->storage                                      = $storage;
        $this->directRecruitmentExpensesServices            = $directRecruitmentExpensesServices;
        $this->directRecruitmentOnboardingCountryServices   = $directRecruitmentOnboardingCountryServices;
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
            'flight_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'flight_date' => 'required|date|date_format:Y-m-d|after:yesterday',
            'expenses' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'checkout_memo_reference_number' => 'required|regex:/^[0-9]*$/|max:23',
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
        $request['post_arrival_cancelled_status'] = Config::get('services.POST_ARRIVAL_CANCELLED_STATUS');
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
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
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->where(function ($query)use ($request) {
                $query->where([
                    ['worker_fomema.fomema_status', 'Unfit'],
                    ['workers.plks_status', 'Pending']
                ])
                ->orWhere([
                    ['workers.cancel_status', $request['post_arrival_cancelled_status']]
                ]);
            })
            ->whereNull('workers.replace_worker_id')
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'worker_fomema.fomema_status', 'workers.date_of_birth', 'workers.gender', 'directrecruitment_workers.agent_id', 'workers.plks_status', 'workers.cancel_status', 'workers.directrecruitment_status')->selectRaw("(CASE WHEN (workers.directrecruitment_status = 'Repatriated') THEN workers.directrecruitment_status 
            WHEN (workers.directrecruitment_status = 'Cancelled') THEN workers.directrecruitment_status
            ELSE worker_fomema.fomema_status END) as status")->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function updateRepatriation($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);
            foreach ($request['workers'] as $workerId) {
                $this->workerRepatriation->create([
                    'worker_id' => $workerId,
                    'flight_number' => $request['flight_number'],
                    'flight_date' => $request['flight_date'],
                    'expenses' => $request['expenses'],
                    'checkout_memo_reference_number' => $request['checkout_memo_reference_number'],
                    'created_by' => $request['modified_by'],
                    'modified_by' => $request['modified_by']
                ]);
                if (request()->hasFile('attachment')) {
                    foreach($request->file('attachment') as $file) {
                        $fileName = $file->getClientOriginalName();
                        $filePath = 'directRecruitment/workers/repatriation/' . $workerId. '/'. $fileName; 
                        $linode = $this->storage::disk('linode');
                        $linode->put($filePath, file_get_contents($file));
                        $fileUrl = $this->storage::disk('linode')->url($filePath);
                        $this->workerRepatriationAttachments->create([
                            'file_id' => $workerId,
                            'file_name' => $fileName,
                            'file_type' => 'Repatriation',
                            'file_url' => $fileUrl,
                            'created_by' => $request['modified_by'],
                            'modified_by' => $request['modified_by']
                        ]);
                    }
                }
            }
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'directrecruitment_status' => 'Repatriated',
                    'fomema_valid_until' => $request['fomema_valid_until'], 
                    'modified_by' => $request['modified_by']
                ]);
                
            $this->directRecruitmentOnboardingCountryServices->updateUtilisedQuota($request['onboarding_country_id'], count($request['workers']), 'decrement');
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        
        // ADD OTHER EXPENSES - Onboarding - Repatriation Expenses (RM)
        $request['expenses_application_id'] = $request['application_id'] ?? 0;
        $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[7];
        $request['expenses_payment_reference_number'] = $request['checkout_memo_reference_number'] ?? '';
        $request['expenses_payment_date'] = Carbon::now();
        $request['expenses_amount'] = $request['expenses'] ?? 0;
        $request['expenses_remarks'] = $request['remarks'] ?? '';
        $this->directRecruitmentExpensesServices->addOtherExpenses($request);
        
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
        $request['post_arrival_cancelled_status'] = Config::get('services.POST_ARRIVAL_CANCELLED_STATUS');
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
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
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->where(function ($query)use ($request) {
                $query->where([
                    ['worker_fomema.fomema_status', 'Unfit'],
                    ['workers.plks_status', 'Pending']
                ])
                ->orWhere([
                    ['workers.cancel_status', $request['post_arrival_cancelled_status']]
                ]);
            })
            ->whereNull('workers.replace_worker_id')
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until','workers.date_of_birth', 'workers.gender', 'directrecruitment_workers.agent_id')
            ->selectRaw("(CASE WHEN (workers.directrecruitment_status = 'Repatriated') THEN workers.directrecruitment_status 
            WHEN (workers.directrecruitment_status = 'Cancelled') THEN workers.directrecruitment_status
            ELSE worker_fomema.fomema_status END) as status")
            ->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }
}