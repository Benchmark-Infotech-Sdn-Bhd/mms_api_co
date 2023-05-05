<?php

namespace App\Services;

use App\Models\FWCMS;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class FWCMSServices
{
    /**
     * @var FWCMS
     */
    private FWCMS $fwcms;

    /**
     * FWCMSServices Constructor
     * @param FWCMS $fwcms
     */
    public function __construct(FWCMS $fwcms)
    {
        $this->fwcms = $fwcms;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'submission_date' => 'required|date|date_format:Y-m-d|before:today',
                'applied_quota' => 'required|regex:/^[0-9]+$/|max:3',
                'status' => 'required',
                'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21'
            ];
    }
     /**
     * @return array
     */
    public function updateValidation(): array
    {
        return
            [
                'id' => 'required',
                'application_id' => 'required',
                'submission_date' => 'required|date|date_format:Y-m-d|before:today',
                'applied_quota' => 'required|regex:/^[0-9]+$/|max:3',
                'status' => 'required',
                'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21'
            ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->fwcms->where('application_id', $request['application_id'])
        ->select('id', 'application_id', 'submission_date', 'applied_quota', 'status', 'ksm_reference_number', 'updated_at')
        ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->fwcms->where('id', $request['id'])->get();
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $this->fwcms->create([
            'application_id' => $request['application_id'] ?? 0,
            'submission_date' => $request['submission_date'] ?? '',
            'applied_quota' => $request['applied_quota'] ?? 0,
            'status' => $request['status'] ?? '',
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['modified_by'] ?? 0
        ]);
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $fwcmsDetails = $this->fwcms->findOrFail($request['id']);
        $fwcmsDetails->application_id       = $request['application_id'] ?? $fwcmsDetails->application_id;
        $fwcmsDetails->submission_date      = $request['submission_date'] ?? $fwcmsDetails->submission_date;
        $fwcmsDetails->applied_quota        = $request['applied_quota'] ?? $fwcmsDetails->applied_quota;
        $fwcmsDetails->status               = $request['status'] ?? $fwcmsDetails->status;
        $fwcmsDetails->ksm_reference_number = $request['ksm_reference_number'] ?? $fwcmsDetails->ksm_reference_number;
        $fwcmsDetails->remarks              = $request['remarks'] ?? $fwcmsDetails->remarks;
        $fwcmsDetails->modified_by          = $request['modified_by'] ?? $fwcmsDetails->modified_by;
        $fwcmsDetails->save();
        return true;
    }
}