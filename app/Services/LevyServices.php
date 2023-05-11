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
     * @return array
     */
    public function createValidation(): array
    {
        return[
            'application_id' => 'required',
            'payment_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'payment_amount' => 'required|decimal:0,2',
            'approved_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'ksm_reference_number' => 'required',
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'approval_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'new_ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21',
        ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'application_id' => 'required',
            'payment_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'payment_amount' => 'required|decimal:0,2',
            'approved_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'ksm_reference_number' => 'required',
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'approval_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'new_ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21',
        ];
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
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
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
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $levyDetails = $this->levy->findOrFail($request['id']);
        $levyDetails->payment_date              = $request['payment_date'] ?? $levyDetails->payment_date;
        $levyDetails->payment_amount            = $request['payment_amount'] ?? $levyDetails->payment_amount;
        $levyDetails->approved_quota            = $request['approved_quota'] ?? $levyDetails->approved_quota;
        $levyDetails->status                    = $request['status'] ?? $levyDetails->status;
        $levyDetails->ksm_reference_number      = $request['ksm_reference_number'] ?? $levyDetails->ksm_reference_number;
        $levyDetails->payment_reference_number  = $request['payment_reference_number'] ?? $levyDetails->payment_reference_number;
        $levyDetails->approval_number           = $request['approval_number'] ?? $levyDetails->approval_number;
        $levyDetails->new_ksm_reference_number  = $request['new_ksm_reference_number'] ?? $levyDetails->new_ksm_reference_number;
        $levyDetails->remarks                   = $request['remarks'] ?? $levyDetails->remarks;
        $levyDetails->modified_by               = $request['modified_by'] ?? $levyDetails->modified_by;
        $levyDetails->save();
        return true;
    }
}