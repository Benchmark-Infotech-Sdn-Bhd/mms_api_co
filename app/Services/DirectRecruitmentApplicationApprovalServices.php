<?php

namespace App\Services;

use App\Models\DirectRecruitmentApplicationApproval;
use App\Models\ApprovalAttachments;
use App\Models\DirectrecruitmentApplications;
use App\Models\FWCMS;
use App\Models\CRMProspectService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Http\Request;

class DirectRecruitmentApplicationApprovalServices
{
    /**
     * @var DirectRecruitmentApplicationApproval
     */
    private DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval;

    /**
     * @var ApprovalAttachments
     */
    private ApprovalAttachments $approvalAttachments;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var fwcms
     */
    private FWCMS $fwcms;
    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;
    /**
     * @var CRMProspectService
     */
    private CRMProspectService $crmProspectService;

    /**
     * Constructor for the class.
     *
     * @param DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval An instance of DirectRecruitmentApplicationApproval.
     * @param ApprovalAttachments $approvalAttachments An instance of ApprovalAttachments.
     * @param Storage $storage An instance of Storage.
     * @param DirectrecruitmentApplications $directrecruitmentApplications An instance of DirectrecruitmentApplications.
     * @param FWCMS $fwcms An instance of FWCMS.
     * @param ApplicationSummaryServices $applicationSummaryServices An instance of ApplicationSummaryServices.
     * @param CRMProspectService $crmProspectService An instance of CRMProspectService.
     */
    public function __construct(
        DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval,
        ApprovalAttachments                  $approvalAttachments,
        Storage                              $storage,
        DirectrecruitmentApplications        $directrecruitmentApplications,
        FWCMS                                $fwcms,
        ApplicationSummaryServices           $applicationSummaryServices,
        CRMProspectService                   $crmProspectService
    )
    {
        $this->directRecruitmentApplicationApproval = $directRecruitmentApplicationApproval;
        $this->approvalAttachments = $approvalAttachments;
        $this->storage = $storage;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->fwcms = $fwcms;
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->crmProspectService = $crmProspectService;
    }

    /**
     * Creates a validation array for the createValidation method.
     *
     * @return array The validation array with keys and their corresponding validation rules.
     */
    public function createValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'ksm_reference_number' => 'required|unique:directrecruitment_application_approval',
                'received_date' => 'required|date|date_format:Y-m-d',
                'valid_until' => 'required|date|date_format:Y-m-d'
            ];
    }

    /**
     * Update validation method.
     *
     * @param mixed $param The parameter used to build the validation rules.
     * @return array The array of validation rules.
     */
    public function updateValidation($param): array
    {
        return
            [
                'id' => 'required',
                'application_id' => 'required',
                'ksm_reference_number' => 'required|unique:directrecruitment_application_approval,ksm_reference_number,' . $param['id'],
                'received_date' => 'required|date|date_format:Y-m-d',
                'valid_until' => 'required|date|date_format:Y-m-d'
            ];
    }

    /**
     * Retrieve a list of direct recruitment application approvals.
     *
     * @param array $request The request parameters.
     * @return mixed The paginated list of direct recruitment application approvals.
     */
    public function list($request): mixed
    {
        return $this->directRecruitmentApplicationApproval
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_applications.id', '=', 'directrecruitment_application_approval.application_id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('directrecruitment_application_approval.application_id', $request['application_id'])
            ->select('directrecruitment_application_approval.id', 'directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.item_name', 'directrecruitment_application_approval.ksm_reference_number', 'directrecruitment_application_approval.received_date', 'directrecruitment_application_approval.valid_until', 'directrecruitment_application_approval.updated_at')
            ->orderBy('directrecruitment_application_approval.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show method.
     *
     * @param mixed $request The request data.
     * @return Builder|Model|object|null Returns the result of the query.
     */
    public function show($request)
    {
        return $this->directRecruitmentApplicationApproval->with(['approvalAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->join('directrecruitment_applications', function ($join) use ($request) {
            $join->on('directrecruitment_applications.id', '=', 'directrecruitment_application_approval.application_id')
                ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
        })->where('directrecruitment_application_approval.id', $request['id'])
            ->first('directrecruitment_application_approval.*');
    }

    /**
     * Create a new entry in the database based on the given request data.
     *
     * @param $request - The request data.
     * @return bool|array Returns true on success or an array with error information if validation fails or invalid user is found.
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $applicationCheck = $this->directrecruitmentApplications->find($request['application_id']);
        if ($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' => true
            ];
        }
        $approvalDetails = $this->createApprovalDetails($request);
        $approvalId = $approvalDetails->id;

        $this->handleFile($request, 'levy_payment_receipt', $approvalId, 'Levy Payment Receipt');
        $this->handleFile($request, 'approval_letter', $approvalId, 'Approval letter');

        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? '';
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[6];
        $request['status'] = 'Submitted';
        $this->applicationSummaryServices->ksmUpdateStatus($request);

        $this->updateApplicationStatus($request, $approvalId);

        return true;
    }

    /**
     * Creates approval details based on the input request.
     *
     * @param array $request The input request containing application details.
     * @return mixed The result of the create method in DirectRecruitmentApplicationApproval.
     */
    protected function createApprovalDetails($request)
    {
        return $this->directRecruitmentApplicationApproval->create([
            'application_id' => $request['application_id'] ?? 0,
            'item_name' => 'Approval Letter',
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'received_date' => $request['received_date'] ?? '',
            'valid_until' => $request['valid_until'] ?? '',
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['modified_by'] ?? 0
        ]);
    }

    /**
     * Handle the uploaded file.
     *
     * @param Request $request The request object.
     * @param string $fileKey The key for the uploaded file.
     * @param int $fileId The ID of the file.
     * @param string $fileType The type of the file.
     *
     * @return void
     */
    protected function handleFile($request, $fileKey, $fileId, $fileType)
    {
        if (request()->hasFile($fileKey)) {
            foreach ($request->file($fileKey) as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/approval/' . $fileKey . '/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->approvalAttachments::create([
                    "file_id" => $fileId,
                    "file_name" => $fileName,
                    "file_type" => $fileType,
                    "file_url" => $fileUrl
                ]);
            }
        }
    }

    /**
     * Updates the status of an application based on approval count and ksm count.
     *
     * @param array $request The request data containing the application id.
     * @param int $approvalId The approval id for the application.
     */
    protected function updateApplicationStatus($request, $approvalId)
    {
        $ksmCount = $this->fwcms->where('application_id', $request['application_id'])
            ->where('status', '!=', 'Rejected')
            ->count('ksm_reference_number');
        $approvalCount = $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])->count('ksm_reference_number');
        if ($ksmCount == $approvalCount) {
            $this->setApplicationStatus($request, 'Completed', Config::get('services.APPROVAL_COMPLETED'));
        } else {
            $this->setApplicationApprovalFlag($request);
        }
    }

    /**
     * Set application status and update related entities.
     *
     * @param mixed $request The request data.
     * @param int $status The new status for the application.
     * @param string $configValue The new config value for the application.
     * @return void
     */
    protected function setApplicationStatus($request, $status, $configValue)
    {
        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
        $applicationDetails->status = $configValue;
        $applicationDetails->approval_flag = 1;
        $applicationDetails->save();
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[6];
        $request['status'] = $status;
        $this->applicationSummaryServices->updateStatus($request);
        $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
        $serviceDetails->status = 0;
        $serviceDetails->save();
    }

    /**
     * Sets the application approval flag to 1.
     *
     * @param array $request The request data.
     * @throws ModelNotFoundException if the direct recruitment application is not found.
     */
    protected function setApplicationApprovalFlag($request)
    {
        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
        $applicationDetails->approval_flag = 1;
        $applicationDetails->save();
    }

    /**
     * Update the application details.
     *
     * @param Request $request The updated application details.
     *
     * @return bool|array Returns true if the application is updated successfully, otherwise returns an array with error information.
     */
    public function update($request): bool|array
    {
        $requestArr = $request->toArray();
        $validator = Validator::make($requestArr, $this->updateValidation($requestArr));
        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }
        $approvalDetails = $this->directRecruitmentApplicationApproval->findOrFail($requestArr['id']);
        $applicationCheck = $this->directrecruitmentApplications->find($requestArr['application_id']);
        if ($this->isInvalidUser($applicationCheck, $approvalDetails, $requestArr)) {
            return ['InvalidUser' => true];
        }
        $this->updateApprovalDetails($approvalDetails, $requestArr);
        $this->handleFileUpload('levy_payment_receipt', 'Levy Payment Receipt', $requestArr['id']);
        $this->handleFileUpload('approval_letter', 'Approval letter', $requestArr['id']);
        $this->updateApplicationStatus($requestArr, $approvalDetails);
        return true;
    }

    /**
     * Check if the user is invalid.
     *
     * @param $applicationCheck - An instance of the application check.
     * @param $approvalDetails - An instance of the approval details.
     * @param $requestArr - An array containing the request details.
     *
     * @return bool Returns true if the user is invalid, false otherwise.
     */
    private function isInvalidUser($applicationCheck, $approvalDetails, $requestArr): bool
    {
        return $applicationCheck->company_id != $requestArr['company_id'] ||
            $requestArr['application_id'] != $approvalDetails->application_id;
    }

    /**
     * Updates the approval details.
     *
     * @param object $approvalDetails The approval details object.
     * @param array $requestArr The request array containing the updated approval details.
     *        - The keys in the array should match the properties of the $approvalDetails object.
     *        - The values in the array should be the updated values for the corresponding properties.
     */
    private function updateApprovalDetails($approvalDetails, $requestArr): void
    {
        $approvalDetails->application_id = $requestArr['application_id'] ?? $approvalDetails->application_id;
        $approvalDetails->ksm_reference_number = $requestArr['ksm_reference_number'] ?? $approvalDetails->ksm_reference_number;
        $approvalDetails->received_date = $requestArr['received_date'] ?? $approvalDetails->received_date;
        $approvalDetails->valid_until = $requestArr['valid_until'] ?? $approvalDetails->valid_until;
        $approvalDetails->modified_by = $requestArr['modified_by'] ?? $approvalDetails->modified_by;
        $approvalDetails->save();
    }

    /**
     * Handles the file upload.
     *
     * @param string $fileNameInRequest The name of the file in the request.
     * @param string $fileType The type of the file.
     * @param string $id The ID associated with the file.
     */
    private function handleFileUpload(string $fileNameInRequest, string $fileType, string $id)
    {
        if (request()->hasFile($fileNameInRequest)) {
            $files = request()->file($fileNameInRequest);
            $this->approvalAttachments->where([['file_id', $id], ['file_type', $fileType]])->delete();
            foreach ($files as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/approval/' . $fileType . '/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $linode->url($filePath);
                $this->approvalAttachments::create(['file_id' => $id, 'file_name' => $fileName, 'file_type' => $fileType, 'file_url' => $fileUrl]);
            }
        }
    }

    /**
     * Delete an attachment.
     *
     * @param array $request An associative array containing the request data.
     *                       The array should have an 'id' key representing the ID of the attachment to be deleted.
     * @return array An associative array representing the response containing the status and message.
     *               The array will have a 'status' key representing the status of the operation,
     *               and a 'message' key representing the message describing the result of the operation.
     *               The 'status' value will be 'success' if the attachment is deleted successfully,
     *               and 'error' otherwise.
     */
    public function deleteAttachment(array $request): array
    {
        // To get data in a separate method
        $data = $this->getDataWithApproval($request['id']);

        // Check the existence of data applying the fail-fast principle
        if (is_null($data)) {
            return $this->getDataNotFoundResponse();
        }

        // Check validity of user by comparing company ids
        if ($this->checkInvalidUser($data->toArray(), $request)) {
            return $this->getInvalidUserResponse();
        }

        // If the user is valid, delete the application and return the response
        return $this->getDeleteResponse($data);
    }

    /**
     * Retrieve data with approval attachments.
     *
     * @param int $id The ID of the data to retrieve.
     *
     * @return mixed The retrieved data with approval attachments, or null if not found.
     */
    private function getDataWithApproval(int $id): mixed
    {
        return $this->approvalAttachments
            ->with([
                'directRecruitmentApplicationApproval' => function ($query) {
                    $query->select('id', 'application_id');
                }
            ])->find($id);
    }

    /**
     * Returns the response when data is not found.
     *
     * @return array An array containing the keys "isDeleted" and "message".
     *               The key "isDeleted" is set to false and the key "message"
     *               is set to "Data not found".
     */
    private function getDataNotFoundResponse(): array
    {
        return ["isDeleted" => false, "message" => "Data not found"];
    }

    /**
     * Check if the user is invalid.
     *
     * @param array $data The data containing directRecruitmentApplicationApproval and application_id.
     * @param array $request The request containing company_id.
     * @return bool Returns true if the user is invalid, false otherwise.
     */
    private function checkInvalidUser(array $data, array $request): bool
    {
        return $this->directrecruitmentApplications
                ->find($data['direct_recruitment_application_approval']['application_id'])->company_id != $request['company_id'];
    }

    /**
     * Returns an array representing an invalid user response.
     *
     * @return array An array with the key 'InvalidUser' set to true.
     */
    private function getInvalidUserResponse(): array
    {
        return ['InvalidUser' => true];
    }

    /**
     * Get the delete response.
     *
     * @param mixed $data An object that needs to be deleted.
     * @return array The delete response array with keys "isDeleted" and "message".
     */
    private function getDeleteResponse($data): array
    {
        return ["isDeleted" => $data->delete(), "message" => "Deleted Successfully"];
    }

}
