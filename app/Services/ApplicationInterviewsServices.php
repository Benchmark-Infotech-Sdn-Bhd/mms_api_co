<?php

namespace App\Services;

use App\Models\ApplicationInterviews;
use App\Models\ApplicationInterviewAttachments;
use App\Models\FWCMS;
use App\Models\DirectrecruitmentApplications;
use App\Models\Levy;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class ApplicationInterviewsServices
{
    /**
     * @var ApplicationInterviews
     */
    private ApplicationInterviews $applicationInterviews;

    /**
     * @var ApplicationInterviewAttachments
     */
    private ApplicationInterviewAttachments $applicationInterviewAttachments;

    /**
     * @var FWCMS
     */
    private FWCMS $fwcms;

    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;

    /**
     * @var Levy
     */
    private Levy $levy;

    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;

    /**
     * ApplicationInterviews Constructor
     * @param ApplicationInterviews $applicationInterviews
     * @param ApplicationInterviewAttachments $applicationInterviewAttachments
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param Levy $levy
     * @param Storage $storage
     * @param FWCMS $fwcms
     * @param ApplicationSummaryServices $applicationSummaryServices ;
     */
    public function __construct(ApplicationInterviews $applicationInterviews, ApplicationInterviewAttachments $applicationInterviewAttachments,  DirectrecruitmentApplications $directrecruitmentApplications, Levy $levy,  Storage $storage, FWCMS $fwcms, ApplicationSummaryServices $applicationSummaryServices)
    {
        $this->applicationInterviews = $applicationInterviews;
        $this->applicationInterviewAttachments = $applicationInterviewAttachments;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->levy = $levy;
        $this->storage = $storage;
        $this->fwcms = $fwcms;
        $this->applicationSummaryServices = $applicationSummaryServices;
    }

    /**
     * createValidation method
     *
     * Creates an array containing validation rules for a new application interview record.
     *
     * @return array An array of validation rules for the new application interview record.
     *     The array contains the following key-value pairs:
     *     - application_id: Required field.
     *     - ksm_reference_number: Required field. Must be unique in the application_interviews table.
     *     - schedule_date: Required field. Must be a valid date in the format Y-m-d and after yesterday.
     *     - status: Required field.
     */
    public function createValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'ksm_reference_number' => 'required|unique:application_interviews',
                'schedule_date' => 'required|date|date_format:Y-m-d|after:yesterday',
                'status' => 'required'
            ];
    }

    /**
     * Updates the validation rules for a specific parameter
     *
     * @param array $param The parameter to update validation for
     * @return array The updated validation rules as an array
     */
    public function updateValidation($param): array
    {
        return
            [
                'id' => 'required',
                'application_id' => 'required',
                'ksm_reference_number' => 'required|unique:application_interviews,ksm_reference_number,'.$param['id'],
                'schedule_date' => 'required|date|date_format:Y-m-d|after:yesterday',
                'status' => 'required'
            ];
    }

    /**
     * List application interviews
     *
     * This method retrieves a paginated list of application interviews based on the provided request data.
     *
     * @param mixed $request The request data containing 'company_id' and 'application_id'
     *
     * @return mixed The paginated list of application interviews
     */
    public function list($request): mixed
    {
        return $this->applicationInterviews
        ->leftJoin('levy', function($join) use ($request){
            $join->on('levy.application_id', '=', 'application_interviews.application_id')
            ->on('levy.ksm_reference_number', '=', 'application_interviews.ksm_reference_number');
          })
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_applications.id', '=', 'application_interviews.application_id')
            ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
        })
        ->where('application_interviews.application_id', $request['application_id'])
        ->select('application_interviews.id', 'application_interviews.ksm_reference_number', 'application_interviews.item_name', 'application_interviews.schedule_date', 'application_interviews.approved_quota', 'application_interviews.approval_date', 'application_interviews.status', 'application_interviews.remarks', 'application_interviews.updated_at', DB::raw('(CASE WHEN levy.status = "Paid" THEN "1" ELSE "0" END) AS edit_application'))
        ->orderBy('application_interviews.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show application interviews
     *
     * @param mixed $request The request containing the company ID and interview ID
     *
     * @return mixed The first row from the query result with the selected columns
     */
    public function show($request): mixed
    {
        return $this->applicationInterviews
        ->leftJoin('levy', function($join) use ($request){
            $join->on('levy.application_id', '=', 'application_interviews.application_id')
            ->on('levy.ksm_reference_number', '=', 'application_interviews.ksm_reference_number');
          })
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_applications.id', '=', 'application_interviews.application_id')
            ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
        })
        ->where('application_interviews.id', $request['id'])->with('applicationInterviewAttachments')
                ->first(['application_interviews.id', 'application_interviews.application_id', 'application_interviews.ksm_reference_number', 'application_interviews.item_name', 'application_interviews.schedule_date', 'application_interviews.approved_quota', 'application_interviews.approval_date', 'application_interviews.status', 'application_interviews.remarks', DB::raw('(CASE WHEN levy.status = "Paid" THEN "1" ELSE "0" END) AS edit_application')]);
    }

    /**
     * Create a new application interview
     *
     * @param Request $request The request object containing the interview data
     *
     * @return bool|array Returns true if the interview is created successfully, otherwise returns an array with error details
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $applicationCheck = $this->directrecruitmentApplications->find($request['application_id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' => true
            ];
        }

        $fwcmsQuota = $this->fwcms->where('ksm_reference_number', $request['ksm_reference_number'])->sum('applied_quota');
        if($request['approved_quota'] > $fwcmsQuota) {
            return [
                'quotaError' => true
            ];
        }

        $applicationInterview = $this->applicationInterviews->create([
            'application_id' => $request['application_id'] ?? 0,
            'item_name' => Config::get('services.APPLICATION_INTERVIEW_ITEM_NAME'),
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'schedule_date' => $request['schedule_date'] ?? '',
            'approved_quota' => !empty($request['approved_quota']) ? ($request['approved_quota'] ?? 0) : 0,
            'approval_date' => !empty($request['approval_date']) ? ($request['approval_date'] ?? null) : null,
            'status' => $request['status'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['modified_by'] ?? 0
        ]);

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/interviews/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->applicationInterviewAttachments::create([
                        "file_id" => $applicationInterview['id'],
                        "file_name" => $fileName,
                        "file_type" => 'proposal',
                        "file_url" =>  $fileUrl
                    ]);
            }
        }

        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? '';
        $request['status'] = $request['status'] ?? '';
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[4];
        $this->applicationSummaryServices->ksmUpdateStatus($request);

        return true;
    }

    /**
     * Update the application interview details.
     *
     * This method updates the application interview details based on the given request.
     * It validates the request data using the updateValidation() method and returns an error array if validation fails.
     * It checks the validity of the specified application and interview ID and returns an error array if invalid.
     * It checks if the approved quota exceeds the quota limit and returns a quota error message if true.
     * It updates the application interview details with the provided request data.
     * It updates the status of the application summary based on the request.
     * It counts the occurrences of KSM (Key Sequence Movement) for the specified application ID.
     * It counts the approved application interviews for the specified application ID.
     * It updates the status of the application details to "Interview Completed" if the status is set to "Approved" and the current status is less than or equal to "Interview Completed"
     * or the current status is "FWCMS Rejected".
     * It deletes the existing interview attachments for the specified interview ID and adds new attachments if provided.
     * It returns true if the update process is successful.
     *
     * @param array $request The request data containing the update details.
     * @return bool|array Returns true if the update is successful. Returns an error array if validation fails or any error occurs during the update process.
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation($request));
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $applicationCheck = $this->directrecruitmentApplications->find($request['application_id']);
        $applicationInterviewsDetails = $this->applicationInterviews->findOrFail($request['id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' => true
            ];
        } else if($request['application_id'] != $applicationInterviewsDetails->application_id) {
            return [
                'InvalidUser' => true
            ];
        }

        $fwcmsQuota = $this->fwcms->where('ksm_reference_number', $request['ksm_reference_number'])->sum('applied_quota');
        if($request['approved_quota'] > $fwcmsQuota) {
            return [
                'quotaError' => true
            ];
        }

        $applicationInterviewsDetails->application_id = $request['application_id'] ?? $applicationInterviewsDetails->application_id;
        $applicationInterviewsDetails->ksm_reference_number = $request['ksm_reference_number'] ?? $applicationInterviewsDetails->ksm_reference_number;
        $applicationInterviewsDetails->item_name = $request['item_name'] ?? $applicationInterviewsDetails->item_name;
        $applicationInterviewsDetails->schedule_date = $request['schedule_date'] ?? $applicationInterviewsDetails->schedule_date;
        $applicationInterviewsDetails->approved_quota = !empty($request['approved_quota']) ? ($request['approved_quota'] ?? $applicationInterviewsDetails->approved_quota) : $applicationInterviewsDetails->approved_quota;
        $applicationInterviewsDetails->approval_date = (isset($request['approval_date']) && !empty($request['approval_date'])) ? ($request['approval_date'] ?? $applicationInterviewsDetails->approval_date) : $applicationInterviewsDetails->approval_date;

        $applicationInterviewsDetails->status = $request['status'] ?? $applicationInterviewsDetails->status;
        $applicationInterviewsDetails->remarks = $request['remarks'] ?? $applicationInterviewsDetails->remarks;
        $applicationInterviewsDetails->modified_by = $request['modified_by'] ?? $applicationInterviewsDetails->modified_by;
        $applicationInterviewsDetails->save();

        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? $applicationInterviewsDetails->ksm_reference_number;
        $request['status'] = $request['status'] ?? $applicationInterviewsDetails->status;
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[4];
        $this->applicationSummaryServices->ksmUpdateStatus($request);

        $ksmCount = $this->fwcms->where('application_id', $request['application_id'])
                    ->where('status', '!=', 'Rejected')
                    ->count('ksm_reference_number');
        $applicationInterviewApprovedCount = $this->applicationInterviews->where('application_id', $request['application_id'])
                        ->where('status', 'Approved')
                        ->count();

            $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);

            if($request['status'] == 'Approved') {
                if(($applicationDetails->status <= Config::get('services.INTERVIEW_COMPLETED')) || $applicationDetails->status == Config::get('services.FWCMS_REJECTED')){
                    $applicationDetails->status = Config::get('services.INTERVIEW_COMPLETED');
                }
                $applicationDetails->save();
            }

            $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[4];
            $request['status'] = 'Completed';
            $this->applicationSummaryServices->updateStatus($request);


        if (request()->hasFile('attachment')){

            $this->applicationInterviewAttachments->where('file_id', $request['id'])->delete();

            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/interviews/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->applicationInterviewAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'proposal',
                    "file_url" =>  $fileUrl
                ]);
            }
        }

        return true;
    }

    /**
     * Delete an attachment.
     *
     * @param mixed $request The request data containing the attachment ID and company ID.
     *
     * @return array An array indicating whether the attachment is deleted successfully and a corresponding message.
     *               - If the attachment is deleted successfully, the "isDeleted" key will be set to true.
     *               - If the attachment is not found, the "isDeleted" key will be set to false and the "message" key will contain an error message.
     *               - If the company ID of the attachment does not match the requested company ID, the "InvalidUser" key will be set to true.
     *
     * @throws Exception If an error occurs during the deletion process.
     */
    public function deleteAttachment($request): mixed
    {
        $data = $this->applicationInterviewAttachments->with(['ApplicationInterviews' => function ($query) {
            $query->select('id', 'application_id');
        }])->find($request['id']);

        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }

        $applicationCheck = $this->directrecruitmentApplications->find($data['ApplicationInterviews']['application_id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' => true
            ];
        }

        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * Returns the dropdown list of KSM reference numbers for a specific application.
     *
     * @param array $request The request data containing the application ID and company ID.
     * @return Collection|array Returns an array of KSM reference numbers if the user is valid and the application type is specified.
     * Returns null if the user is invalid or the application type is not specified.
     */
    public function dropdownKsmReferenceNumber($request)
    {
        $applicationCheck = $this->directrecruitmentApplications->find($request['id']);

        if ($applicationCheck->company_id !== $request['company_id']) {
            return ['InvalidUser' => true];
        }

        if (!empty($request['application_type'])) {
            return $this->getApplicationDataByType($request);
        }
    }

    /**
     * Get application data by type
     *
     * @param array $request The request data containing the application type and id
     * @return Collection|void Returns the application data based on the specified type and id
     */
    private function getApplicationDataByType($request)
    {
        switch ($request['application_type']) {
            case 'FWCMS':
            case 'INTERVIEW':
                return $this->queryApplicationData($this->fwcms, $request['id'], ['id', 'ksm_reference_number', 'applied_quota as approved_quota'], 'services.APPLICATION_INTERVIEW_KSM_REFERENCE_STATUS');

            case 'LEVY':
                return $this->queryApplicationData($this->applicationInterviews, $request['id'], ['id', 'ksm_reference_number', 'approved_quota'], 'services.APPLICATION_INTERVIEW_KSM_REFERENCE_STATUS');

            case 'APPROVAL':
                return $this->queryApplicationData($this->levy, $request['id'], ['id', 'new_ksm_reference_number as ksm_reference_number'], 'services.APPLICATION_LEVY_KSM_REFERENCE_STATUS');
        }
    }

    /**
     * Query Application Data
     *
     * Retrieve application data from the specified $model based on the given parameters.
     *
     * @param string|Model $model The model used to query the application data.
     * @param int $applicationId The application id to filter the data.
     * @param array $selectFields The fields to be selected in the query.
     * @param string $statusConfig The configuration key containing the allowed status values.
     *
     * @return Collection The collection of application data matching the given parameters.
     */
    private function queryApplicationData($model, $applicationId, $selectFields, $statusConfig)
    {
        return $model::where('application_id', $applicationId)
            ->whereIn('status', Config::get($statusConfig))
            ->select($selectFields)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
