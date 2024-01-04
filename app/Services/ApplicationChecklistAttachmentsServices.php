<?php

namespace App\Services;

use App\Models\ApplicationChecklistAttachments;
use App\Models\DocumentChecklist;
use App\Models\DirectrecruitmentApplications;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class ApplicationChecklistAttachmentsServices
{
    private DocumentChecklist $documentChecklist;
    private ValidationServices $validationServices;
    private Storage $storage;
    private DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices;
    private DirectRecruitmentServices $directRecruitmentServices;
    private ApplicationSummaryServices $applicationSummaryServices;
    private DirectrecruitmentApplications $directrecruitmentApplications;
    private ApplicationChecklistAttachments $applicationChecklistAttachments;

    /**
     * Constructs a new instance of the class.
     *
     * @param ApplicationChecklistAttachments $applicationChecklistAttachments The application checklist attachments.
     * @param DocumentChecklist $documentChecklist The document checklist.
     * @param ValidationServices $validationServices The validation services.
     * @param Storage $storage The storage implementation.
     * @param DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices The direct recruitment application checklist services.
     * @param DirectRecruitmentServices $directRecruitmentServices The direct recruitment services.
     * @param ApplicationSummaryServices $applicationSummaryServices The application summary services.
     * @param DirectrecruitmentApplications $directrecruitmentApplications The direct recruitment applications.
     */
    public function __construct(ApplicationChecklistAttachments $applicationChecklistAttachments, DocumentChecklist $documentChecklist, ValidationServices $validationServices,
    Storage $storage,DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices,
    DirectRecruitmentServices $directRecruitmentServices, ApplicationSummaryServices $applicationSummaryServices, DirectrecruitmentApplications $directrecruitmentApplications)
    {
        $this->applicationChecklistAttachments = $applicationChecklistAttachments;
        $this->documentChecklist = $documentChecklist;
        $this->validationServices = $validationServices;
        $this->storage = $storage;
        $this->directRecruitmentApplicationChecklistServices = $directRecruitmentApplicationChecklistServices;
        $this->directRecruitmentServices = $directRecruitmentServices;
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $params = $request->all();
        if(!($this->validationServices->validate($params,$this->applicationChecklistAttachments->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $companyArray = [];
        array_push($companyArray, $params['company_id']);

        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklistServices->showBasedOnApplication(["application_id" => $params['application_id'], "company_id" => $companyArray]);

        if(is_null($directRecruitmentApplicationChecklist)) {
            return [
                'InvalidUser' => true
            ];
        }
        $documentChecklistCheck = $this->documentChecklist->with(['sectors' => function ($query) {
            $query->select('id', 'company_id');
        }])->find($request['document_checklist_id']);

        if(is_null($documentChecklistCheck)) {
            return [
                'InvalidUser' => true
            ];
        }
        if($documentChecklistCheck['sectors']['company_id'] != $params['company_id']) {
            return [
                'InvalidUser' => true
            ];
        }
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/application/checklist/'.$params['application_id'].'/'. $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->applicationChecklistAttachments->create([
                            "document_checklist_id" => $params['document_checklist_id'],
                            "application_id" => $params['application_id'],
                            "application_checklist_id" => $directRecruitmentApplicationChecklist['id'] ?? 0,
                            "file_type" => 'checklist',
                            "file_url" =>  $fileUrl ,
                            "created_by"    => $params['created_by'] ?? 0,
                            "modified_by"   => $params['created_by'] ?? 0
                        ]);
            }
        }else{
            return [
                "isUploaded" => false,
                "message" => "Document not found"
            ];
        }
        $count = $this->applicationChecklistAttachments->whereNull('deleted_at')
        ->where(function ($query) use ($params) {
            if (isset($params['application_id'])) {
                $query->where('application_id',$params['application_id']);
            }
        })->count('id');
        $directRecruitmentApplicationChecklist->modified_on = Carbon::now();
        if($count == 1){
            $res = $this->directRecruitmentServices->updateStatus(['id' => $params['application_id'] , 'status' => Config::get('services.CHECKLIST_COMPLETED')]);
            $directRecruitmentApplicationChecklist->application_checklist_status = 'Completed';
            $directRecruitmentApplicationChecklist->modified_by = $user['id'] ?? $directRecruitmentApplicationChecklist['modified_by'];
            $directRecruitmentApplicationChecklist->submitted_on = Carbon::now();

            $params['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[2];
            $params['status'] = 'Completed';
            $this->applicationSummaryServices->updateStatus($params);
        }
        $directRecruitmentApplicationChecklist->save();
        return [
            "isUploaded" => true,
            "message" => "Document uploaded Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function delete($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();

        $directrecruitmentApplicationAttachment = $this->applicationChecklistAttachments
                                    ->join('directrecruitment_applications', function ($join) use($request) {
                                        $join->on('directrecruitment_applications.id', '=', 'application_checklist_attachments.application_id')
                                        ->where('directrecruitment_applications.company_id', $request['company_id']);
                                    })->select('application_checklist_attachments.*')->find($request['id']);
        if(is_null($directrecruitmentApplicationAttachment)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }

        $companyArray = [];
        array_push($companyArray, $request['company_id']);

        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklistServices->showBasedOnApplication(["application_id" =>  $directrecruitmentApplicationAttachment['application_id'], "company_id" => $companyArray]);

        $deleteApplicationChecklistAttachment = $directrecruitmentApplicationAttachment->delete();
        $res = [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];

        if($deleteApplicationChecklistAttachment){

            $count = $this->applicationChecklistAttachments->whereNull('deleted_at')
            ->where(function ($query) use ($directrecruitmentApplicationAttachment) {
                if (isset($directrecruitmentApplicationAttachment['application_id'])) {
                    $query->where('application_checklist_attachments.application_id',$directrecruitmentApplicationAttachment['application_id']);
                }
            })->count('id');
            $directRecruitmentApplicationChecklist->modified_on = Carbon::now();
            if($count == 0){
                $resUpdate = $this->directRecruitmentServices->updateStatus(['id' => $directrecruitmentApplicationAttachment['application_id'] , 'status' => Config::get('services.PROPOSAL_SUBMITTED')]);
                $directRecruitmentApplicationChecklist->application_checklist_status = 'Pending';
                $directRecruitmentApplicationChecklist->modified_by = $user['id'] ?? $directRecruitmentApplicationChecklist['modified_by'];

                $request['application_id'] = $directrecruitmentApplicationAttachment['application_id'];
                $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[2];
                $this->applicationSummaryServices->deleteStatus($request);
            }
            $directRecruitmentApplicationChecklist->save();
        }
        return $res;
    }

    /**
     * Lists the application details and generates a checklist based on the given request.
     *
     * @param mixed $request The request data.
     * @return mixed The generated checklist or error response.
     */
    public function list($request): mixed
    {
        if (!$this->validateRequest($request)) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $applicationDetails = $this->getApplicationDetails($request);

        if (is_null($applicationDetails)) {
            return [
                'unauthorizedError' => true
            ];
        }

        return $this->generateChecklist($request);
    }

    /**
     * Validates the request.
     *
     * @param mixed $request The request data to be validated.
     * @return bool True if the request is valid, false otherwise.
     */
    private function validateRequest($request): bool
    {
        return $this->validationServices->validate($request, ['application_id' => 'required']);
    }

    /**
     * Retrieves the details of a specific application.
     *
     * @param array $request The request parameters containing the company ID and application ID.
     *     The format of the $request array is as follows:
     *     [
     *         'company_id' => [int], // The ID of the company associated with the application.
     *         'application_id' => [int], // The ID of the application to retrieve details for.
     *     ]
     * @return mixed|null Returns the application details if found, or null if the application does not exist.
     */
    private function getApplicationDetails($request)
    {
        return $this->directrecruitmentApplications->whereIn('company_id', $request['company_id'])->find($request['application_id']);
    }

    /**
     * Generates a checklist based on the given request data.
     *
     * @param mixed $request The request data containing the sector ID and application ID.
     * @return LengthAwarePaginator The paginated checklist result.
     */
    private function generateChecklist($request)
    {
        return $this->documentChecklist->where('document_checklist.sector_id', $request['sector_id'])
            ->leftJoin('application_checklist_attachments', function ($join) use ($request) {
                $this->generateJoinClause($join, $request);
            })
            ->leftJoin('directrecruitment_application_checklist', 'directrecruitment_application_checklist.id', 'application_checklist_attachments.application_checklist_id')
            ->with(["applicationChecklistAttachments" => function ($attachment) use ($request) {
                $attachment->where('application_id', $request['application_id']);
            }])
            ->orderBy('document_checklist.created_at', 'DESC')
            ->select($this->fields())
            ->distinct('document_checklist.id')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Generate the join clause for a query.
     *
     * @param JoinClause $join The join clause to modify.
     * @param mixed $request The request data used for generating the join clause.
     * @return void
     */
    private function generateJoinClause($join, $request): void
    {
        $join->on('application_checklist_attachments.document_checklist_id', '=', 'document_checklist.id')
            ->where('application_checklist_attachments.application_id', '=', $request['application_id']);
    }

    /**
     * Returns an array of all the fields used by the method.
     *
     * @return array An array containing all the fields used by the method.
     */
    private function fields(): array
    {
        return [
            'document_checklist.id',
            'document_checklist.sector_id',
            'document_checklist.document_title',
            'directrecruitment_application_checklist.application_id',
            'directrecruitment_application_checklist.application_checklist_status',
            'directrecruitment_application_checklist.submitted_on',
            'directrecruitment_application_checklist.modified_on',
            'directrecruitment_application_checklist.created_by',
            'directrecruitment_application_checklist.modified_by',
            'directrecruitment_application_checklist.created_at',
            'directrecruitment_application_checklist.updated_at',
            'directrecruitment_application_checklist.deleted_at',
            'directrecruitment_application_checklist.remarks',
        ];
    }
}
