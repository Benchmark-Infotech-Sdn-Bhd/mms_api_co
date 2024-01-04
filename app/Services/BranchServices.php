<?php


namespace App\Services;

use App\Models\Branch;
use App\Models\Services;
use App\Models\State;
use App\Models\BranchesServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;

class BranchServices
{
    /**
     * @var branch
     */
    private Branch $branch;
    /**
     * @var services
     */
    private Services $services;
    /**
     * @var branchesServices
     */
    private BranchesServices $branchesServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Constructs a new instance of the class.
     *
     * @param Branch $branch The branch object.
     * @param Services $services The services object.
     * @param BranchesServices $branchesServices The branches services object.
     * @param AuthServices $authServices The authentication services object.
     */
    public function __construct(Branch $branch, Services $services, BranchesServices $branchesServices, AuthServices $authServices)
    {
        $this->branch = $branch;
        $this->services = $services;
        $this->branchesServices = $branchesServices;
        $this->authServices = $authServices;
    }

    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @param $request - The request object containing input data.
     * @return bool The validation error messages if validation fails, otherwise false.
     */
    public function inputValidation($request)
    {
        if (!($this->branch->validate($request->all()))) {
            return $this->branch->errors();
        }
        return false;
    }

    /**
     * Validates the update request.
     *
     * @param $request - The update request object.
     *
     * @return false The validation result. It returns the branch errors if validation fails, otherwise false.
     */
    public function updateValidation($request)
    {
        if (!($this->branch->validateUpdation($request->all()))) {
            return $this->branch->errors();
        }
        return false;
    }

    /**
     * Validates if the status of the branch is valid.
     *
     * @param mixed $request The request object for the status validation.
     * @param mixed $rules The rules to validate the status against.
     * @return false Returns the error message if validation fails, otherwise returns false.
     */
    public function updateStatusValidation($request, $rules)
    {
        if (!($this->branch->validateStatus($request, $rules))) {
            return $this->branch->errors();
        }
        return false;
    }

    /**
     * Creates a new branch record.
     *
     * @param array $request The request data containing branch information.
     * @return mixed Returns the created branch record.
     */
    public function create($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];

        $branchData = $this->createBranch($request);
        $this->attachServicesToBranch($request['service_type'], $branchData->id);

        return $branchData;
    }

    /**
     * Creates a new branch object and persists it in the database.
     *
     * @param array $request The array containing branch data.
     *                      The array should have the following keys:
     *                      - branch_name: The name of the branch.
     *                      - state: The state of the branch.
     *                      - city: The city of the branch.
     *                      - branch_address: The address of the branch.
     *                      - postcode: The postcode of the branch.
     *                      - remarks: Any additional remarks about the branch.
     *                      - created_by: The ID of the user who created the branch.
     *                      - company_id: The ID of the company the branch belongs to.
     *
     * @return Branch The newly created branch object.
     */
    private function createBranch(array $request): Branch
    {
        $branchData = [
            'branch_name' => $request["branch_name"],
            'state' => $request["state"],
            'city' => $request["city"],
            'branch_address' => $request["branch_address"],
            'postcode' => $request["postcode"],
            'remarks' => $request["remarks"],
            'created_by' => $request["created_by"],
            'company_id' => $request["company_id"],
        ];

        return $this->branch::create($branchData);
    }

    /**
     * Attaches services to a branch.
     *
     * @param array $serviceType The array of service IDs.
     * @param int $branchId The ID of the branch.
     * @return void
     */
    private function attachServicesToBranch(array $serviceType, int $branchId): void
    {
        foreach ($serviceType as $service_id) {
            $servicesFromRequest = $this->services->where('id', '=', $service_id)->select('id', 'service_name', 'status')->get();

            foreach ($servicesFromRequest as $service) {
                $serviceData = [
                    'branch_id' => $branchId,
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'status' => $service->status,
                ];

                $this->branchesServices::create($serviceData);
            }
        }
    }

    /**
     * Returns a paginated list of branches with their associated branch services.
     *
     * @param array $request The request data containing the company ID and search parameter.
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated list of branches with their associated branch services.
     */
    public function list($request)
    {
        return $this->branch::with('branchServices')
            ->whereIn('company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if (!empty($request['search_param'])) {
                    $query->where('branch_name', 'like', '%' . $request['search_param'] . '%')
                        ->orWhere('state', 'like', '%' . $request['search_param'] . '%')
                        ->orWhere('city', 'like', '%' . $request['search_param'] . '%');
                }
            })
            ->orderBy('branch.created_at', 'DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show the branch details with related branch services.
     *
     * @param array $request The request data.
     * @return mixed Returns the branch details with related branch services.
     */
    public function show($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);
        return $this->branch::with('branchServices')->whereIn('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Update branch data.
     *
     * @param array $requestData The request data including the updated branch details.
     * @return array An associative array with "isUpdated" boolean indicating if the branch data was successfully updated, and "message" string with a success message.
     */
    public function update($requestData): array
    {
        list($user, $requestData) = $this->getAuthenticatedUserDetails($requestData);

        $branchData = $this->branch::where('company_id', $requestData['company_id'])->find($requestData['id']);
        if (is_null($branchData)) {
            return $this->returnNotFoundData();
        }

        $branchesServiceType = $this->branchesServices->where('branch_id', $requestData['id'])
            ->select('service_id', 'service_name')
            ->get()
            ->pluck('service_id')
            ->all();

        $serviceDataToAdd = array_diff($requestData['service_type'], $branchesServiceType);
        $serviceDataToRemove = array_diff($branchesServiceType, $requestData['service_type']);

        if (!empty($serviceDataToAdd)) {
            $this->createBranchServiceData($serviceDataToAdd, $requestData['id']);
        }
        if (!empty($serviceDataToRemove)) {
            $this->removeBranchServiceData($serviceDataToRemove, $requestData['id']);
        }

        return [
            "isUpdated" => $branchData->update($requestData),
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Retrieves the authenticated user details.
     *
     * @param array $requestData The request data for modification.
     * @return array The authenticated user details (user, requestData).
     */
    private function getAuthenticatedUserDetails($requestData): array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData['modified_by'] = $user['id'];
        $requestData['company_id'] = $user['company_id'];

        return array($user, $requestData);
    }

    /**
     * Returns an array containing data for a "Not Found" condition.
     *
     * @return array The array containing the "Not Found" data.
     *               The array structure includes the following keys:
     *               - "isUpdated" (boolean): Indicates whether the data was updated. Always set to `false`.
     *               - "message" (string): A message indicating that the data was not found.
     *
     * @since Version 1.0.0
     */
    private function returnNotFoundData(): array
    {
        return [
            "isUpdated" => false,
            "message" => "Data not found"
        ];
    }

    /**
     * Creates branch service data for a given service.
     *
     * @param mixed $serviceDataToAdd The service data to add.
     * @param int $branchId The ID of the branch.
     * @return void
     */
    private function createBranchServiceData($serviceDataToAdd, $branchId): void
    {
        foreach ($serviceDataToAdd as $serviceType) {
            $serviceTypeData = $this->services->where('id', '=', $serviceType)->select('id', 'service_name', 'status')->get();
            foreach ($serviceTypeData as $service) {
                $this->branchesServices::create([
                    'branch_id' => $branchId,
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'status' => $service->status,
                ]);
            }
        }
    }

    /**
     * Removes the branch service data.
     *
     * @param array $serviceDataToRemove An array of service types to remove.
     * @param int $branchId The ID of the branch.
     * @return void
     */
    private function removeBranchServiceData($serviceDataToRemove, $branchId): void
    {
        foreach ($serviceDataToRemove as $serviceType) {
            $this->branchesServices::where('branch_id', $branchId)->where('service_id', $serviceType)->delete();
        }
    }

    /**
     * Deletes a branch and its related records.
     *
     * @param array $request The request data containing the branch ID and other parameters.
     * @return array The result of the delete operation containing the deletion status and message.
     */
    public function delete($request): array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $data = $this->branch::whereIn('company_id', $request['company_id'])->find($request['id']);
        if (is_null($data)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $data->branchServices()->delete();
        $data->delete();
        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * Returns a dropdown list of branches.
     *
     * @param int $companyId The ID of the company.
     *
     * @return mixed The dropdown list of branches.
     */
    public function dropDown($companyId): mixed
    {
        return $this->branch::where('status', '=', 1)
            ->whereIn('company_id', $companyId)
            ->select('id', 'branch_name')
            ->orderBy('branch.created_at', 'DESC')
            ->get();
    }

    /**
     * Update the status of a branch related to the authenticated user's company
     * @param array $requestData Array with 'id' and 'status' keys
     * @return array Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function updateStatus(array $requestData): array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $companyIds = $this->authServices->getCompanyIds($user);

        $branch = $this->findBranchByCompanyId($requestData['id'], $companyIds);

        if (is_null($branch)) {
            return [
                "isUpdated" => false,
                "message" => "Data not found"
            ];
        }

        $branch->status = $requestData['status'];

        return [
            "isUpdated" => $branch->save(),
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Find branch by company id
     * @param string $branchId
     * @param array $companyIds
     * @return Branch|null
     */
    private function findBranchByCompanyId(string $branchId, array $companyIds): ?Branch
    {
        return $this->branch::whereIn('company_id', $companyIds)->find($branchId);
    }
}
