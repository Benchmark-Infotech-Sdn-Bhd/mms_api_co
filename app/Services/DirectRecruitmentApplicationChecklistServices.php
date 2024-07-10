<?php

namespace App\Services;

use App\Models\DirectRecruitmentApplicationChecklist;
use Illuminate\Database\Eloquent\Model;

class DirectRecruitmentApplicationChecklistServices
{
    private DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist;
    private ValidationServices $validationServices;

    /**
     * Constructor method for the class.
     *
     * @param DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist The direct recruitment application checklist object.
     * @param ValidationServices $validationServices The validation services object.
     * @return void
     */
    public function __construct(
        DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist,
        ValidationServices                    $validationServices
    )
    {
        $this->directRecruitmentApplicationChecklist = $directRecruitmentApplicationChecklist;
        $this->validationServices = $validationServices;
    }

    /**
     * Create a new DirectRecruitmentApplicationChecklist.
     *
     * @param mixed $request The request object containing the data for creating the checklist.
     * @return mixed Returns the created DirectRecruitmentApplicationChecklist object if successful, otherwise returns an associative array with an "validate" key containing the validation
     * errors.
     */
    public function create($request): mixed
    {
        // Extract and prepare data upfront
        $createdBy = $request['created_by'] ?? 0;

        // Preparing the input array
        $data = [
            'application_id' => isset($request['application_id']) ? (int)$request['application_id'] : 0,
            'item_name' => $request['item_name'] ?? 'Document Checklist',
            'application_checklist_status' => $request['application_checklist_status'] ?? 'Pending',
            'created_by' => $createdBy,
            'modified_by' => $createdBy
        ];

        // Validate the request
        if (!($this->validationServices->validate($request, $this->directRecruitmentApplicationChecklist->rules))) {
            return ['validate' => $this->validationServices->errors()];
        }

        // Create the application checklist
        return $this->directRecruitmentApplicationChecklist->create($data);
    }

    /**
     * Update method for the class.
     *
     * @param array $request The update request.
     * @return array The result of the update operation.
     */
    public function update($request)
    {
        $validationErrors = $this->validateRequest($request);
        if ($validationErrors) {
            return ['validate' => $validationErrors];
        }
        return $this->updateChecklist($request);
    }

    /**
     * Validates the given request against the rules for update.
     *
     * @param mixed $request The request data to be validated.
     * @return array|null Returns an array containing validation errors if validation fails, otherwise returns null.
     */
    private function validateRequest($request)
    {
        if (!$this->validationServices->validate($request, $this->directRecruitmentApplicationChecklist->rulesForUpdation)) {
            return $this->validationServices->errors();
        }
        return null;
    }

    /**
     * Updates the checklist based on the provided request data.
     *
     * @param mixed $request The request data containing the checklist details to be updated.
     * @return array Returns an array with the following elements:
     *               - isUpdated: A boolean indicating if the update was successful.
     *               - message: A string message indicating the status of the update.
     *                Possible values for isUpdated:
     *                   - true: The update was successful.
     *                   - false: The update failed because the checklist data was not found.
     *                Possible values for message:
     *                   - "Updated Successfully": The update was successful.
     *                   - "Data not found": The update failed because the checklist data was not found.
     */
    private function updateChecklist($request)
    {
        $directRecruitmentApplicationChecklist =
            $this->directRecruitmentApplicationChecklist->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_applications.id', '=', 'directrecruitment_application_checklist.application_id')
                    ->where('directrecruitment_applications.company_id', $request['company_id']);
            })
                ->where('directrecruitment_application_checklist.id', $request['id'])
                ->first('directrecruitment_application_checklist.*');
        if (is_null($directRecruitmentApplicationChecklist)) {
            return [
                "isUpdated" => false,
                "message" => "Data not found"
            ];
        }

        return [
            "isUpdated" => $directRecruitmentApplicationChecklist->update([
                'id' => $request['id'],
                'application_id' => (int)($request['application_id'] ?? $directRecruitmentApplicationChecklist['application_id']),
                'item_name' => $request['item_name'] ?? $directRecruitmentApplicationChecklist['item_name'],
                'application_checklist_status' => $request['application_checklist_status'] ?? $directRecruitmentApplicationChecklist['application_checklist_status'],
                'remarks' => $request['remarks'] ?? $directRecruitmentApplicationChecklist['remarks'],
                'file_url' => $request['file_url'] ?? $directRecruitmentApplicationChecklist['file_url'],
                'modified_by' => $request['modified_by'] ?? $directRecruitmentApplicationChecklist['modified_by']
            ]),
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Fetches and returns the recruitment application details for the given request.
     *
     * @param mixed $request The request data containing the company ID and application ID.
     * @return array|Model|null Returns the recruitment application details if the request is valid, otherwise returns the validation status.
     */
    public function show($request)
    {
        $validationStatus = $this->validateRequestId($request);
        if (!is_null($validationStatus)) {
            return $validationStatus;
        }       
        return $this->fetchRecruitmentApplication($request['company_id'], $request['id']);
    }

    /**
     * Validates the request ID against the required rule.
     *
     * @param mixed $request The request data containing the ID to be validated.
     * @return array|null Returns an array containing validation errors if validation fails, otherwise returns null.
     */
    private function validateRequestId($request): ?array
    {
        if (!$this->validationServices->validate($request, ['id' => 'required'])) {
            return [
                'validationErrors' => $this->validationServices->errors()
            ];
        }
        return null;
    }

    /**
     * Fetches the recruitment application checklist for the specified company and application ID.
     *
     * @param int $companyId The ID of the company associated with the recruitment application.
     * @param int $appId The ID of the recruitment application checklist.
     * @return Model|null Returns the recruitment application checklist if found, otherwise returns null.
     */
    private function fetchRecruitmentApplication($companyId, $appId)
    {        
        return $this->directRecruitmentApplicationChecklist->join('directrecruitment_applications', function ($join) use ($companyId) {
            $join->on('directrecruitment_applications.id', '=', 'directrecruitment_application_checklist.application_id')
                ->whereIn('directrecruitment_applications.crm_prospect_id', $companyId);
        })
            ->where('directrecruitment_application_checklist.id', $appId)
            ->first('directrecruitment_application_checklist.*');
    }

    /**
     * Retrieves and returns the application checklist based on the given request.
     *
     * @param mixed $request The request data to be used for retrieving the application checklist.
     * @return mixed Returns an array containing the application checklist if the request is valid, otherwise returns the validation errors.
     */
    public function showBasedOnApplication_1($request): mixed
    {
        $validationResult = $this->validateRequestApplicationId($request);
       
        if (!is_null($validationResult)) {
            return $validationResult;
        }

        // print_r($request);
        // die();

        return $this->retrieveApplicationChecklist($request);
    }

    public function showBasedOnApplication($request) : mixed
    {
        if(!($this->validationServices->validate($request,['application_id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklist->where('application_id',$request['application_id'])->first();
        if(is_null($directRecruitmentApplicationChecklist)){
            return [
                "isPresent" => false,
                "message"=> "Data not found"
            ];
        }
        return $directRecruitmentApplicationChecklist;
    }

    /**
     * Validates the request Application ID against the required rule.
     *
     * @param mixed $request The request data containing the application Id to be validated.
     * @return array|null Returns an array containing validation errors if validation fails, otherwise returns null.
     */
    private function validateRequestApplicationId($request): ?array
    {
        if (!$this->validationServices->validate($request, ['application_id' => 'required'])) {
            return [
                'validationErrors' => $this->validationServices->errors()
            ];
        }
        return null;
    }

    /**
     * Retrieves the application checklist based on the given request.
     *
     * @param array $request The request data containing 'company_id' and 'application_id' keys.
     * @return object|null Returns the application checklist as an object if found, otherwise returns null.
     */
    private function retrieveApplicationChecklist($request): mixed
    {      
        return $this->directRecruitmentApplicationChecklist
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_applications.id', '=', 'directrecruitment_application_checklist.application_id')
                   ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('directrecruitment_application_checklist.application_id', $request['application_id'])
            ->first('directrecruitment_application_checklist.*');
    }
}
