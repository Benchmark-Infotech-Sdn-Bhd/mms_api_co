<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Countries;
use App\Models\DirectRecruitmentOnboardingCountry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class AgentServices
{
    public const REQUEST_STATUS = 'status';
    public const REQUEST_ID = 'id';
    public const REQUEST_COMPANY_ID = 'company_id';
    public const REQUEST_COUNTRY_STATUS = 'status';

    public const STATUS_ACTIVE = 1;

    public const MESSAGE_NOT_FOUND = "Data not found";
    public const MESSAGE_UPDATED_SUCCESSFULLY = "Updated Successfully";
    public const MESSAGE_COUNTRY_INACTIVE =
        '“You are not allowed to update agent status due to an inactive Country assigned, Kindly “Reactive the country associated with this agent” or ”assign to a new country to the agent”';
    private Agent $agent;
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    private ValidationServices $validationServices;
    private Countries $countries;

    /**
     * Constructor method.
     *
     * @param Agent $agent The agent object.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The direct recruitment onboarding country object.
     * @param ValidationServices $validationServices The validation services object.
     * @param Countries $countries The countries object.
     *
     * @return void
     */
    public function __construct(Agent $agent, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, ValidationServices $validationServices, Countries $countries)
    {
        $this->agent = $agent;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->validationServices = $validationServices;
        $this->countries = $countries;
    }

    /**
     * Create method.
     *
     * @param mixed $request The request data.
     *
     * @return mixed The response data.
     */
    public function create($request): mixed
    {
        if (!$this->isValidRequest($request)) {
            return $this->validationErrorsResponse();
        }

        if (!$this->isValidCountry($request)) {
            return $this->invalidUserResponse();
        }

        return $this->createAgent($request);
    }

    /**
     * Checks if the given request is valid.
     *
     * @param mixed $request The request to be validated.
     * @return bool Returns true if the request is valid, false otherwise.
     */
    private function isValidRequest($request): bool
    {
        return $this->validationServices->validate($request, $this->agent->rules);
    }

    /**
     * Returns an array containing the validation errors.
     *
     * @return array The array containing the validation errors.
     */
    private function validationErrorsResponse(): array
    {
        return ['validate' => $this->validationServices->errors()];
    }

    /**
     * Checks if the given country is valid for the provided request.
     *
     * @param mixed $request The request that contains the 'company_id' and 'country_id' fields.
     * @return bool Returns true if the country is valid for the request, false otherwise.
     */
    private function isValidCountry($request): bool
    {
        return !is_null($this->countries->where('company_id', $request['company_id'])->find($request['country_id']));
    }

    /**
     * Returns an array indicating that the user is invalid.
     *
     * @return array An array containing the key 'InvalidUser' set to true.
     */
    private function invalidUserResponse(): array
    {
        return ['InvalidUser' => true];
    }

    /**
     * Creates a new agent from the given request data.
     *
     * @param mixed $request The data of the agent to be created. It should contain the following keys:
     *                       - agent_name: (string) The name of the agent.
     *                       - country_id: (int) The ID of the country for the agent.
     *                       - city: (string) The city of the agent.
     *                       - person_in_charge: (string) The person in charge of the agent.
     *                       - pic_contact_number: (int) The contact number of the person in charge.
     *                       - email_address: (string) The email address of the agent.
     *                       - company_address: (string) The address of the agent's company.
     *                       - created_by: (int) The ID of the user who created the agent.
     *                       - modified_by: (int) The ID of the user who modified the agent.
     *                       - company_id: (int) The ID of the company.
     *                       The 'agent_code' key will be auto-generated.
     *
     * @return mixed The created agent object. Check the documentation for the specific type of agent object.
     */
    private function createAgent($request): mixed
    {
        $request['agent_code'] = $this->generateAgentCode();

        return $this->agent->create([
            'agent_name' => $request['agent_name'] ?? '',
            'country_id' => (int)$request['country_id'],
            'city' => $request['city'] ?? '',
            'person_in_charge' => $request['person_in_charge'] ?? '',
            'pic_contact_number' => (int)$request['pic_contact_number'] ?? '',
            'email_address' => $request['email_address'] ?? '',
            'company_address' => $request['company_address'] ?? '',
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0,
            'company_id' => $request['company_id'] ?? 0,
            'agent_code' => $request['agent_code']
        ]);
    }

    /**
     * Update the agent data based on the given request.
     *
     * @param mixed $request The request data to update the agent.
     * @return array Returns an array with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isUpdated": A boolean indicating if the agent data was successfully updated.
     *  - "message": A message indicating the result of the update operation.
     */
    public function update($request): array
    {
        if (!($this->validationServices->validate($request, $this->agent->rulesForUpdation($request['id'])))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $agent = $this->agent->whereIn('company_id', $request['company_id'])->find($request['id']);

        if (is_null($agent)) {
            return [
                "isUpdated" => false,
                "message" => "Data not found"
            ];
        }

        $updatedData = $this->updateAgentData($agent, $request);

        return [
            "isUpdated" => $agent->update($updatedData),
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Updates the agent data with the given request.
     *
     * @param object $agent The agent object to be updated.
     * @param array $request The request containing the updated agent data.
     * @return array Returns an array with the updated agent data:
     *               - 'agent_name' (string) The updated agent name.
     *               - 'country_id' (int) The updated country ID.
     *               - 'city' (string) The updated city.
     *               - 'person_in_charge' (string) The updated person in charge.
     *               - 'pic_contact_number' (int) The updated PIC contact number.
     *               - 'email_address' (string) The updated email address.
     *               - 'company_address' (string) The updated company address.
     *               - 'modified_by' (string) The updated modified by.
     */
    private function updateAgentData($agent, $request)
    {
        return [
            'agent_name' => $request['agent_name'] ?? $agent->agent_name,
            'country_id' => (int)$request['country_id'] ?? $agent->country_id,
            'city' => $request['city'] ?? $agent->city,
            'person_in_charge' => $request['person_in_charge'] ?? $agent->person_in_charge,
            'pic_contact_number' => (int)$request['pic_contact_number'] ?? $agent->pic_contact_number,
            'email_address' => $request['email_address'] ?? $agent->email_address,
            'company_address' => $request['company_address'] ?? $agent->company_address,
            'modified_by' => $request['modified_by'] ?? $agent->modified_by
        ];
    }

    /**
     * Deletes a record from the database.
     *
     * @param mixed $request The request containing the data needed for deletion.
     *
     * @return array Returns an array containing the deletion result and message.
     * The array will have the following keys:
     * - "isDeleted" (bool): Whether the deletion was successful or not.
     * - "message" (string): A message indicating the status of the deletion.
     * If the validation fails, the array will have an additional key:
     * - "validate" (array): An array containing the validation errors.
     */
    public function delete($request): array
    {
        if (!($this->validationServices->validate($request, ['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $agent = $this->agent->whereIn('company_id', $request['company_id'])->find($request['id']);
        if (is_null($agent)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $agent->delete(),
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * Retrieves the agent with countries based on the given request.
     *
     * @param mixed $request The request to be used for retrieving the agent.
     * @return array|Model|null Returns the agent with countries if the request is valid, otherwise returns an array with validation errors.
     */
    public function show($request)
    {
        if (!$this->validateRequest($request, ['id' => 'required'])) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->retrieveAgentWithCountries($request);
    }

    /**
     * Validates the given request against the specified rules.
     *
     * @param mixed $request The request to be validated.
     * @param array $rules An array of validation rules.
     * @return bool Returns true if the request passes validation, false otherwise.
     */
    private function validateRequest($request, array $rules): bool
    {
        return $this->validationServices->validate($request, $rules);
    }

    /**
     * Retrieves the agent with the associated countries based on the given request.
     *
     * @param array $request The request containing the following keys:
     *     - company_id: The company ID.
     *     - id: The agent ID.
     *
     * @return Model|null The agent model with the associated countries, or null if not found.
     */
    private function retrieveAgentWithCountries($request)
    {
        return $this->agent->with('countries')->whereIn('company_id', $request['company_id'])->where('id', $request['id'])->first();
    }

    /**
     * Returns a paginated list of agents based on the given search request.
     *
     * @param array $request The search request parameters.
     * @return mixed Returns an array with a 'validate' key containing the validation errors, if the search request is invalid.
     *               Otherwise, returns a paginated list of agents.
     */
    public function list($request): mixed
    {
        if ($this->isSearchParamValid($request)) {
            return [
                'validate' => $this->validationServices->errors(),
            ];
        }

        return $this->agent
            ->join('countries', 'countries.id', '=', 'agent.country_id')
            ->whereIn('agent.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                $this->applySearchQuery($query, $request);
            })
            ->select('agent.id', 'agent.agent_name', 'countries.country_name', 'agent.city', 'agent.person_in_charge', 'agent.status', 'agent.agent_code')
            ->orderBy('agent.created_at', 'DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Checks if the search parameter in the given request is valid.
     *
     * @param mixed $request The request containing the search parameter.
     * @return bool Returns true if the search parameter is valid, false otherwise.
     */
    private function isSearchParamValid($request): bool
    {
        return !empty($request['search_param']) &&
            !$this->validationServices->validate($request, ['search_param' => 'required|min:3']);
    }

    /**
     * Applies the search query to the given query builder.
     *
     * @param Builder $query The query builder to apply the search query to.
     * @param array $request The request containing the search parameter.
     * @return void
     */
    private function applySearchQuery($query, $request)
    {
        if (!empty($request['search_param'])) {
            $query->where('agent_name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('countries.country_name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('city', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('person_in_charge', 'like', '%' . $request['search_param'] . '%');
        }
    }

    /**
     * Updates the status of an agent and returns the response.
     *
     * @param array $request The request containing agent details.
     * @return array Returns the response array containing the updated status.
     */
    public function updateStatus($request): array
    {
        if (!$this->isRequestValid($request)) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $agent = $this->findAgentWithCountries($request);

        if (is_null($agent)) {
            return $this->failureResponse(self::MESSAGE_NOT_FOUND);
        }

        if ($this->isActivationRequest($request) and !$this->isCountryActive($agent)) {
            return $this->failureResponse(self::MESSAGE_COUNTRY_INACTIVE);
        }

        return $this->updateStatusAndRespond($agent, $request[self::REQUEST_STATUS]);
    }

    /**
     * Checks if the given request is valid based on specified rules.
     *
     * @param mixed $request The request to be checked for validity.
     * @return bool Returns true if
     */
    private function isRequestValid($request): bool
    {
        return $this->validationServices->validate($request, [
            self::REQUEST_ID => 'required',
            self::REQUEST_STATUS => 'required|regex:/^[0-1]+$/|max:1'
        ]);
    }

    /**
     * Finds an agent with associated countries.
     *
     * @param mixed $request The request containing the company ID and agent ID.
     * @return mixed Returns the agent with associated countries if found, null otherwise.
     */
    private function findAgentWithCountries($request)
    {
        return $this->agent->with('countries')->whereIn('company_id', $request[self::REQUEST_COMPANY_ID])->find($request[self::REQUEST_ID]);
    }

    /**
     * Checks if the given request is an activation request.
     *
     * @param mixed $request The request to be checked.
     * @return bool Returns true if the request is an activation request, false otherwise.
     */
    private function isActivationRequest($request): bool
    {
        return $request[self::REQUEST_STATUS] == self::STATUS_ACTIVE;
    }

    /**
     * Checks if the given agent's country is active.
     *
     * @param array $agent The agent's information.
     * @return bool Returns true if the agent's country is active, false otherwise.
     */
    private function isCountryActive($agent): bool
    {
        return !is_null($agent['countries']) && $agent['countries'][self::REQUEST_COUNTRY_STATUS] == self::STATUS_ACTIVE;
    }

    /**
     * Generates a failure response with the given message.
     *
     * @param string $message The error message to be included in the failure response.
     * @return array Returns an array representing the failure response with the 'isUpdated' flag set to false and the provided message.
     */
    private function failureResponse($message): array
    {
        return [
            "isUpdated" => false,
            "message" => $message
        ];
    }

    /**
     * Updates the status of the given agent and responds with the update result.
     *
     * @param object $agent The agent object to update the status for.
     * @param mixed $status The new status for the agent.
     * @return array Returns an array with the update result.
     *     - bool isUpdated: Indicates whether the update was successful.
     *     - string message: A message describing the update result.
     */
    private function updateStatusAndRespond($agent, $status): array
    {
        $agent->status = $status;
        $isUpdated = $agent->save() == 1;

        return [
            "isUpdated" => $isUpdated,
            "message" => $isUpdated ? self::MESSAGE_UPDATED_SUCCESSFULLY : self::MESSAGE_NOT_FOUND
        ];
    }

    /**
     * Updates the status of agents based on the specified countries.
     *
     * @param array $request An array containing the country ID and the new status.
     *                      Example: ['country_id' => 1, 'status' => 'active']
     *
     * @return array Returns an array with two keys:
     *               - "isUpdated" indicates whether the update operation was successful (true) or not (false).
     *               - "message" provides a descriptive message about the update status.
     *                 Example: ['isUpdated' => true, 'message' => 'Updated Successfully']
     */
    public function updateStatusBasedOnCountries($request): array
    {
        $agent = $this->agent->where('country_id', $request['country_id'])
            ->update(['status' => $request['status']]);
        return [
            "isUpdated" => $agent,
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Retrieves a dropdown list of agents based on the given request.
     *
     * @param array $request The request parameters for retrieving the dropdown list.
     * @return mixed Returns a collection of agents with id and agent_name fields, ordered by created_at.
     */
    public function dropdown($request): mixed
    {
        if (!empty($request['onboarding_country_id'])) {
            $country = $this->directRecruitmentOnboardingCountry->find($request['onboarding_country_id']);
        }
        $countryId = $country->country_id ?? '';
        return $this->agent
            ->whereIn('company_id', $request['company_id'])
            ->where('status', 1)
            ->where(function ($query) use ($request, $countryId) {
                if (!empty($request['onboarding_country_id'])) {
                    $query->where('country_id', '=', $countryId);
                }
            })
            ->select('id', 'agent_name')->orderBy('agent.created_at', 'DESC')->get();
    }

    /**
     * Generates a unique agent code.
     *
     * @return string Returns a randomly generated agent code that does not already exist in the database.
     */
    public function generateAgentCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Agent::where('agent_code', $code)->exists());
        return $code;
    }

    /**
     * Updates the agent code for the specified agent.
     *
     * @param array $request The request containing company_id and id of the agent.
     * @return array Returns an array with 'isUpdated' key indicating whether the agent code was updated successfully,
     *               and 'message' key providing a status message.
     */
    public function updateAgentCode($request): array
    {
        $agentDetails = $this->agent->where('company_id', $request['company_id'])->find($request['id']);
        if (is_null($agentDetails)) {
            return [
                'InvalidUser' => true
            ];
        }
        $request['agent_code'] = $this->generateAgentCode();

        $agent = $this->agent->where('id', $request['id'])
            ->update(['agent_code' => $request['agent_code']]);
        return [
            "isUpdated" => $agent,
            "message" => "Updated Successfully"
        ];
    }
}
