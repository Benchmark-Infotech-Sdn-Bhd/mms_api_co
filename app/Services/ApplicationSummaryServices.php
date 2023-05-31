<?php

namespace App\Services;

use App\Models\ApplicationSummary;
use App\Models\DirectrecruitmentApplications;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class ApplicationSummaryServices
{
    /**
     * @var ApplicationSummary
     */
    private ApplicationSummary $applicationSummary;

    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;

    /**
     * ApplicationSummaryServices Constructor
     * @param ApplicationSummary $applicationSummary
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     */
    public function __construct(ApplicationSummary $applicationSummary, DirectrecruitmentApplications $directrecruitmentApplications)
    {
        $this->applicationSummary = $applicationSummary;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->applicationSummary->where('application_id', $request['application_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['ksm_reference_number']) && !empty($request['ksm_reference_number'])) {
                $query->where('ksm_reference_number', $request['ksm_reference_number']);
                $query->orWhere('ksm_reference_number', null);
            }
        })
        ->select('id', 'application_id', 'action', 'status', 'created_at', 'updated_at', 'ksm_reference_number')
        ->orderBy('id', 'asc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function updateStatus($request): bool|array
    {
        $applicationSummary = $this->applicationSummary->where([
            ['application_id', $request['application_id']],
            ['action', $request['action']],
            ['ksm_reference_number', null]
        ])->first(['id', 'application_id', 'action', 'status', 'created_by', 'modified_by', 'created_at', 'updated_at', 'ksm_reference_number']);

        if(is_null($applicationSummary)){
            $this->applicationSummary->create([
                'application_id' => $request['application_id'] ?? 0,
                'action' => $request['action'] ?? '',
                'status' => $request['status'] ?? '',
                'created_by' =>  $request['created_by'] ?? 0,
                'modified_by' =>  $request['created_by'] ?? 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }else{
            $applicationSummary->update([
                'status' => $request['status'] ?? '',
                'modified_by' =>  $request['created_by'] ?? 0,
                'updated_at' => Carbon::now()
            ]);
        }
        
        return true;
    }

    /**
     *
     * @param $request
     * @return mixed
     */    
    public function deleteStatus($request): mixed
    {   
        $applicationSummary = $this->applicationSummary->where([
            ['application_id', $request['application_id']],
            ['action', $request['action']]
        ])->first();
        if(is_null($applicationSummary)){
            return false;
        }else{
            $applicationSummary->delete();
        }
        return true;
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function ksmUpdateStatus($request): bool|array
    {
        $applicationSummary = $this->applicationSummary->where([
            ['application_id', $request['application_id']],
            ['action', $request['action']],
            ['ksm_reference_number', $request['ksm_reference_number']]
        ])->first(['id', 'application_id', 'action', 'status', 'created_by', 'modified_by', 'created_at', 'updated_at', 'ksm_reference_number']);

        if(is_null($applicationSummary)){
            $this->applicationSummary->create([
                'application_id' => $request['application_id'] ?? 0,
                'action' => $request['action'] ?? '',
                'status' => $request['status'] ?? '',
                'created_by' =>  $request['created_by'] ?? 0,
                'modified_by' =>  $request['created_by'] ?? 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'ksm_reference_number' => $request['ksm_reference_number'] ?? ''
            ]);
        }else{
            $applicationSummary->update([
                'status' => $request['status'] ?? '',
                'modified_by' =>  $request['created_by'] ?? 0,
                'updated_at' => Carbon::now()
            ]);
        }
        return true;
    }
    
}