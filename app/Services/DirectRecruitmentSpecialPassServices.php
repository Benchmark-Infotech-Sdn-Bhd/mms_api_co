<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\SpecialPassAttachments;
use App\Models\Workers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirectRecruitmentSpecialPassServices
{
    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var SpecialPassAttachments
     */
    private SpecialPassAttachments $specialPassAttachments;
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
     * @param SpecialPassAttachments $specialPassAttachments
     * @param Workers $workers
     * @param Storage $storage
     */
    public function __construct(DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, SpecialPassAttachments $specialPassAttachments, Workers $workers, Storage $storage)
    {
        $this->directRecruitmentPostArrivalStatus   = $directRecruitmentPostArrivalStatus;
        $this->specialPassAttachments               = $specialPassAttachments;
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
    public function submissionValidation(): array
    {
        return [
            'submission_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * @return array
     */
    public function validityValidation(): array
    {
        return [
            'valid_until' => 'required|date|date_format:Y-m-d|after:yesterday',
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
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.special_pass' => 1
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.special_pass_submission_date')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function updateSubmission($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->submissionValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'special_pass_submission_date' => $request['submission_date'], 
                    'modified_by' => $request['modified_by']
                ]);
            if(request()->hasFile('attachment')) {
                foreach($request->file('attachment') as $file) {
                    $fileName = $file->getClientOriginalName();
                    $filePath = 'directRecruitment/workers/specialPass/'. Carbon::now()->format('Ymd') . '/' . $fileName; 
                    $linode = $this->storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = $this->storage::disk('linode')->url($filePath);
                }
                foreach ($request['workers'] as $workerId) {
                    $this->specialPassAttachments->create([
                        'file_id' => $workerId,
                        'file_name' => $fileName,
                        'file_type' => 'Special Pass Attachment',
                        'file_url' => $fileUrl,
                        'created_by' => $request['modified_by'],
                        'modified_by' => $request['modified_by']
                    ]);
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
    public function updateValidity($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->validityValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);
            $submissionValidation = $this->workers
                                    ->where('special_pass_submission_date', NULL)
                                    ->whereIn('id', $request['workers'])
                                    ->count('id');
            if($submissionValidation > 0) {
                return [
                    'submissionError' => true
                ];
            }
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'special_pass_valid_until' => $request['valid_until'],
                    'modified_by' => $request['modified_by']
                ]);
            if(request()->hasFile('attachment')) {
                foreach($request->file('attachment') as $file) {
                    $fileName = $file->getClientOriginalName();
                    $filePath = 'directRecruitment/workers/specialPass/'. Carbon::now()->format('Ymd') . '/' . $fileName; 
                    $linode = $this->storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = $this->storage::disk('linode')->url($filePath);
                }
                foreach ($request['workers'] as $workerId) {
                    $this->specialPassAttachments->create([
                        'file_id' => $workerId,
                        'file_name' => $fileName,
                        'file_type' => 'Special Pass Attachment',
                        'file_url' => $fileUrl,
                        'created_by' => $request['modified_by'],
                        'modified_by' => $request['modified_by']
                    ]);
                }
            }
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }
}