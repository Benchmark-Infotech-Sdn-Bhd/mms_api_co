<?php

namespace App\Services;

use App\Models\Levy;
use App\Models\DirectrecruitmentApplications;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class LevyServices
{
    /**
     * @var Levy
     */
    private Levy $levy;
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * LevyServices Constructor
     * @param Levy $levy
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     */
    public function __construct(Levy $levy, DirectrecruitmentApplications $directrecruitmentApplications)
    {
        $this->levy = $levy;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->levy->where('application_id', $request['application_id'])
        ->select('id', 'application_id', 'item', 'payment_date', 'payment_amount', 'approved_quota', 'status')
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->levy->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function create($request): bool|array
    {
        $this->levy->create([
            'application_id' => $request['application_id'] ?? 0,
            'item' => $request['item'] ?? 'Levy Details',
            'payment_date' => $request['payment_date'] ?? '',
            'payment_amount' => $request['payment_amount'] ?? 0,
            'approved_quota' => $request['approved_quota'] ?? 0,
            'status' => $request['status'] ?? 'Paid',
            'ksm_reference_number' =>  $request['ksm_reference_number'] ?? '',
            'payment_reference_number' =>  $request['payment_reference_number'] ?? '',
            'approval_number' =>  $request['approval_number'] ?? '',
            'new_ksm_reference_number' =>  $request['new_ksm_reference_number'] ?? '',
            'remarks' =>  $request['remarks'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['created_by'] ?? 0
        ]);
        return true;
    }
}