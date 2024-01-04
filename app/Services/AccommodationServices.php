<?php


namespace App\Services;

use App\Models\Accommodation;
use App\Models\AccommodationAttachments;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccommodationServices
{
    /**
     * @var accommodation
     */
    private Accommodation $accommodation;
    /**
     * @var accommodationAttachments
     */
    private AccommodationAttachments $accommodationAttachments;
    /**
     * @var Vendor
     */
    private Vendor $vendor;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Constructor method for the class.
     *
     * @param Accommodation $accommodation The instance of Accommodation class.
     * @param AccommodationAttachments $accommodationAttachments The instance of AccommodationAttachments class.
     * @param Vendor $vendor The instance of Vendor class.
     * @param Storage $storage The instance of Storage class.
     * @param AuthServices $authServices The instance of AuthServices class.
     */
    public function __construct(Accommodation $accommodation, AccommodationAttachments $accommodationAttachments, Vendor $vendor, Storage $storage, AuthServices $authServices)
    {
        $this->accommodation = $accommodation;
        $this->accommodationAttachments = $accommodationAttachments;
        $this->vendor = $vendor;
        $this->storage = $storage;
        $this->authServices = $authServices;
    }

    /**
     * Validate the input request.
     *
     * @param mixed $request The input request to be validated.
     */
    public function inputValidation($request)
    {
        if (!($this->accommodation->validate($request->all()))) {
            return $this->accommodation->errors();
        }
    }

    /**
     * Validate the update request for the accommodation.
     *
     * @param object $request The object representing the update request.
     *
     */
    public function updateValidation($request)
    {
        if (!($this->accommodation->validateUpdation($request->all()))) {
            return $this->accommodation->errors();
        }
    }

    /**
     * Creates a new accommodation.
     *
     * @param $request - The request object containing the input data.
     * @return Accommodation|mixed If successful, returns the created Accommodation instance.
     *                           If vendor is not found, returns an array with an 'unauthorizedError' key.
     *                           Otherwise, returns null.
     */
    public function create($request): mixed
    {
        $input = $request->all();
        $user = $this->getAuthenticatedUser();
        $input['created_by'] = $user['id'];

        $vendor = $this->vendor
            ->where('company_id', $user['company_id'])
            ->find($request['vendor_id']);

        if (is_null($vendor)) {
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }

        $createdAccommodation = $this->createAccommodation($input);

        if ($request->hasFile('attachment')) {
            $this->uploadFiles($request->file('attachment'), $createdAccommodation->id);
        }

        return $createdAccommodation;
    }

    /**
     * Create a new accommodation.
     *
     * @param array $inputData The data used to create the accommodation.
     *                        The array should contain the following keys:
     *                        - name: The name of the accommodation.
     *                        - location: The location of the accommodation.
     *                        - maximum_pax_per_unit: The maximum number of people allowed per unit.
     *                        - deposit: The deposit amount for the accommodation.
     *                        - rent_per_month: The monthly rent for the accommodation.
     *                        - vendor_id: The ID of the vendor associated with the accommodation.
     *                        - tnb_bill_account_Number: The TNB bill account number for the accommodation.
     *                        - water_bill_account_Number: The water bill account number for the accommodation.
     *                        - created_by: The user who created the accommodation.
     *
     * @return mixed The newly created accommodation.
     */
    public function createAccommodation(array $inputData)
    {
        return $this->accommodation::create([
            'name' => $inputData["name"],
            'location' => $inputData["location"],
            'maximum_pax_per_unit' => $inputData["maximum_pax_per_unit"],
            'deposit' => $inputData["deposit"],
            'rent_per_month' => $inputData["rent_per_month"],
            'vendor_id' => $inputData["vendor_id"],
            'tnb_bill_account_Number' => $inputData["tnb_bill_account_Number"],
            'water_bill_account_Number' => $inputData["water_bill_account_Number"],
            'created_by' => $inputData['created_by']
        ]);
    }

    /**
     * Upload multiple files for accommodation.
     *
     * @param array $files A array of files to be uploaded.
     * @param int $accommodationId The ID of the accommodation to associate the files with.
     *
     * @return void
     */
    public function uploadFiles($files, $accommodationId)
    {
        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = '/vendor/accommodation/' . $fileName;

            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));

            $fileUrl = $linode->url($filePath);

            $this->accommodationAttachments::create([
                "file_id" => $accommodationId,
                "file_name" => $fileName,
                "file_type" => 'accommodation',
                "file_url" => $fileUrl
            ]);
        }
    }

    /**
     * Get a paginated list of accommodations.
     *
     * @param $request - The request object containing the filters and pagination information.
     *
     * @return LengthAwarePaginator The paginated list of accommodations.
     */
    public function list($request)
    {
        $companyID = $this->getAuthenticatedUser()['company_id'];
        $paginateRowSize = Config::get('services.paginate_row');

        return $this->accommodation::with('vendor', 'accommodationAttachments')
            ->join('vendors', function ($query) use ($companyID) {
                $query->on('vendors.id', '=', 'accommodation.vendor_id')
                    ->where('vendors.company_id', $companyID);
            })
            ->where(function ($query) use ($request) {
                $this->filterAccommodationsBasedOnInput($query, $request);
            })
            ->select('accommodation.*')
            ->orderBy('accommodation.created_at', 'DESC')
            ->paginate($paginateRowSize);
    }


    /**
     * Filter accommodations based on the given input data.
     *
     * @param mixed $query The query object to which the filters will be applied.
     * @param array $request The request data containing the filter parameters.
     *                       The array may contain the following keys:
     *                       - vendor_id: The ID of the vendor to filter by.
     *                       - search_param: The search keyword to filter by.
     *
     * @return void
     */
    protected function filterAccommodationsBasedOnInput($query, $request)
    {
        if (!empty($request['vendor_id'])) {
            $query->where('vendor_id', '=', $request['vendor_id']);
        }
        if (!empty($request['search_param'])) {
            $query->where('vendor_id', '=', $request['vendor_id'])
                ->where('name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('location', 'like', '%' . $request['search_param'] . '%');
        }
    }

    /**
     * Show the details of accommodation.
     *
     * @param int $id The ID of the accommodation.
     *
     * @return mixed The details of the accommodation.
     */
    public function show($id): mixed
    {
        $userWithCompanyId = $this->getAuthenticatedUserWithCompanyId();
        return $this->getAccommodationWithAttachments($userWithCompanyId, $id);
    }

    /**
     * Get the authenticated user with company ID.
     *
     * @return array The authenticated user with company ID.
     */
    private function getAuthenticatedUserWithCompanyId(): array
    {
        $user = $this->getAuthenticatedUser();
        $user['company_id'] = $this->authServices->getCompanyIds($user);
        return $user;
    }

    /**
     * Get accommodation with its attachments.
     *
     * @param array $userWithCompanyId The user with company ID. The array should contain the following keys:
     *                                - company_id: The ID of the company.
     * @param int $id The ID of the accommodation to retrieve.
     *
     * @return mixed The accommodation with attachments.
     */
    private function getAccommodationWithAttachments(array $userWithCompanyId, int $id): mixed
    {
        return $this->accommodation::with([
            'accommodationAttachments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
        ])
            ->join('vendors', function ($query) use ($userWithCompanyId) {
                $query->on('vendors.id', '=', 'accommodation.vendor_id')
                    ->whereIn('vendors.company_id', $userWithCompanyId['company_id']);
            })
            ->select('accommodation.*')
            ->find($id);
    }

    /**
     * Update an existing accommodation.
     *
     * @param mixed $request The request object containing the update data.
     *
     * @return array The update result. The array will contain the following keys:
     *               - isUpdated: A boolean indicating whether the update was successful.
     *               - message: A message indicating the result of the update.
     */
    public function update($request): array
    {
        $user = $this->getAuthenticatedUser();
        $accommodationData = $this->getUserWithCompanyId($user, $request['id']);

        if (is_null($accommodationData)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }

        $accommodationData['modified_by'] = $user['id'];

        if ($request->hasFile('attachment')) {
            $attachmentData = $this->newFilesUpload($request, $accommodationData['id']);
            $accommodationData = $this->accommodationAttachments::create($attachmentData);
        }

        return [
            "isUpdated" => $accommodationData->update($accommodationData),
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Get the user with the specified company ID.
     *
     * @param array $user The user data containing the company ID.
     * @param int $id The ID of the accommodation.
     *
     * @return mixed The user's accommodation with the specified ID.
     */
    private function getUserWithCompanyId($user, $id)
    {
        return $this->accommodation
            ->join('vendors', function ($query) use ($user) {
                $query->on('vendors.id', '=', 'accommodation.vendor_id')
                    ->where('vendors.company_id', $user['company_id']);
            })
            ->select('accommodation.*')
            ->find($id);
    }

    /**
     * Upload new files.
     *
     * @param Request $request The request object containing the files to be uploaded.
     * @param int $id The ID of the file.
     *
     * @return array|null The data of the uploaded file, or null if no files were uploaded.
     */
    private function newFilesUpload($request, $id)
    {
        $fileData = null;

        foreach ($request->file('attachment') as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = '/vendor/accommodation/' . $fileName;
            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));
            $fileUrl = $linode->url($filePath);

            $fileData = [
                "file_id" => $id,
                "file_name" => $fileName,
                "file_type" => 'accommodation',
                "file_url" => $fileUrl
            ];
        }

        return $fileData;
    }

    /**
     * Deletes an accommodation.
     *
     * @param mixed $request The request data used to delete the accommodation.
     *                       It should contain the following keys:
     *                       - id: The ID of the accommodation to be deleted.
     *
     * @return array The result of the delete operation represented as an associative array.
     *               The array will contain the following keys:
     *               - isDeleted: A boolean indicating whether the accommodation was deleted or not.
     *               - message: A string message indicating the status of the delete operation.
     *                          Will be "Data not found" if the accommodation was not found,
     *                          or "Deleted Successfully" if the accommodation was deleted successfully.
     */
    public function delete($request)
    {
        $user = $this->getAuthenticatedUser();
        $data = $this->findByCompanyIdAndId($user['company_id'], $request['id']);

        if (is_null($data)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }

        $this->deleteAccommodationAndAttachments($data);

        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * Get the authenticated user.
     *
     * @return array The authenticated user.
     */
    private function getAuthenticatedUser(): array
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * Find an accommodation by company ID and ID.
     *
     * @param int $companyId The ID of the company.
     * @param int $id The ID of the accommodation.
     * @return Accommodation|null The found accommodation or null if not found.
     */
    private function findByCompanyIdAndId(int $companyId, int $id): ?Accommodation
    {
        return $this->accommodation
            ->join('vendors', function ($query) use ($companyId) {
                $query->on('vendors.id', '=', 'accommodation.vendor_id')
                    ->where('vendors.company_id', $companyId);
            })
            ->select('accommodation.*')
            ->find($id);
    }

    /**
     * Delete the given accommodation and its attachments.
     *
     * @param Accommodation $accommodation The accommodation to delete.
     * @return void
     */
    private function deleteAccommodationAndAttachments(Accommodation $accommodation): void
    {
        $accommodation->accommodationAttachments()->delete();
        $accommodation->delete();
    }

    /**
     * Delete an attachment.
     *
     * @param Request $request The request object containing the ID of the attachment.
     * @return array An array with the result of the deletion operation.
     * The array will contain the following keys:
     * - "isDeleted": A boolean indicating whether the attachment was deleted successfully.
     * - "message": A string with a status message.
     * If the attachment is not found, the array will have the following values:
     * - "isDeleted" set to false.
     * - "message" set to "Data not found".
     */
    public function deleteAttachment($request)
    {
        $attachment = $this->getAttachmentByCompanyIdAndId($request->id);
        if (is_null($attachment)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $attachment->delete(),
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * Get an attachment by company ID and ID.
     *
     * @param int $id The ID of the attachment.
     * @return mixed - The found attachment or null if not found.
     */
    private function getAttachmentByCompanyIdAndId($id)
    {
        $user = $this->getAuthenticatedUser();

        return $this->accommodationAttachments
            ->join('accommodation', 'accommodation.id', 'accommodation_attachments.file_id')
            ->join('vendors', function ($query) use ($user) {
                $query->on('vendors.id', '=', 'accommodation.vendor_id')
                    ->where('vendors.company_id', $user['company_id']);
            })
            ->select('accommodation_attachments.*')
            ->find($id);
    }

}


