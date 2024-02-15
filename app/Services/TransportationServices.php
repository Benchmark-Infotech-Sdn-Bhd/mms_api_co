<?php


namespace App\Services;

use App\Models\Transportation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use App\Models\TransportationAttachments;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;
use App\Services\RolesServices;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Str;

class TransportationServices
{
    public const DEFAULT_VALUE = 0;
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => 'Unauthorized'];
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const MESSAGE_TRANSPORTATION_NOT_CREATED = 'Transportation data not created';


    /**
     * @var transportation
     */
    private Transportation $transportation;
    /**
     * @var transportationAttachments
     */
    private TransportationAttachments $transportationAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var Role
     */
    private Role $role;
    /**
     * @var User
     */
    private User $user;
    /**
     * @var Vendor
     */
    private Vendor $vendor;
    /**
     * @var RolesServices
     */
    private RolesServices $rolesServices;

    /**
     * TransportationServices constructor.
     *
     * @param Transportation $transportation Instance of the Transportation class
     * @param TransportationAttachments $transportationAttachments Instance of the TransportationAttachments class
     * @param Storage $storage Instance of the Storage class
     * @param AuthServices $authServices Instance of the AuthServices class
     * @param Role $role Instance of the Role class
     * @param User $user Instance of the User class
     * @param Vendor $vendor Instance of the Vendor class
     * @param RolesServices $rolesServices Instance of the RolesServices class
     *
     * @return void
     */
    public function __construct(
        Transportation              $transportation,
        TransportationAttachments   $transportationAttachments,
        Storage                     $storage,
        AuthServices                $authServices,
        Role                        $role,
        User                        $user,
        Vendor                      $vendor,
        RolesServices               $rolesServices
    )
    {
        $this->transportation = $transportation;
        $this->transportationAttachments = $transportationAttachments;
        $this->storage = $storage;
        $this->authServices = $authServices;
        $this->role = $role;
        $this->user = $user;
        $this->vendor = $vendor;
        $this->rolesServices = $rolesServices;
    }

    /**
     * Get the Authenticated User data
     *
     * @return mixed Returns the authenticated user data.
     *
     */
    private function getAuthenticatedUser(): mixed
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * Enriches the given request data with user details.
     *
     * @param array $request The request data to be enriched.
     * @return array Returns the enriched request data.
     */
    private function enrichRequestWithUserDetails($request): array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $request;
    }

	 /**
     * Create the transportation
     *
     * @param $request The request data containing the create transportation data
     *
     * @return mixed Returns the created transportation record.
     *
     */
    public function create($request): mixed
    {
        $user = $this->getAuthenticatedUser();
        $request['created_by'] = $user['id'];

        $vendor = $this->vendor
        ->where('company_id', $user['company_id'])
        ->find($request['vendor_id']);

        if(is_null($vendor)){
            return self::ERROR_UNAUTHORIZED;
        }

        $transportationData = $this->createTransportation($request);
        $transportationId = $transportationData->id;

        $this->uploadAttachment($request, $transportationId);

        if(isset($request["assigned_supervisor"]) && $request["assigned_supervisor"] == 1){

            $role = $this->getRole($user);

            if(empty($role)){

                $addRole['name'] = Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR');
                $addRole['company_id'] = $user['company_id'];
                $addRole['special_permission'] = 0;
                $addRole['created_by'] = $user['id'];

                $this->rolesServices->create($addRole);

                $role = $this->getRole($user);

            }

            $res = $this->createSupervisorUser($request,$role,$user,$transportationId);

            if($res){
                return $transportationData;
            }

            $data = $this->transportation::findorfail($transportationData->id);
            $data->transportationAttachments()->delete();
            $transportationData->delete();

            return [
                "isCreated" => false,
                "message"=> self::MESSAGE_TRANSPORTATION_NOT_CREATED
            ];
        }

        return $transportationData;
    }
    /**
     * create transportation.
     *
     * @param array $request The request data containing the driver_name, driver_contact_number, driver_email, vehicle_type, number_plate, vehicle_capacity, vendor_id, assigned_supervisor
     *
     * @return mixed Returns the created vendor record.
     */
    private function createTransportation($request): mixed
    {
        return $this->transportation::create([
            'driver_name' => $request["driver_name"],
            'driver_contact_number' => $request["driver_contact_number"],
            'driver_email' => $request["driver_email"] ?? '',
            'vehicle_type' => $request["vehicle_type"],
            'number_plate' => $request["number_plate"],
            'vehicle_capacity' => $request["vehicle_capacity"],
            'vendor_id' => $request["vendor_id"],
            'assigned_supervisor' => $request["assigned_supervisor"] ?? 0,
            'created_by' => $request["created_by"],
        ]);
    }

    /**
     * Upload attachment of transportation.
     *
     * @param array $request
     *              attachment (file)
     * @param int $transportationId
     *
     * @return void
     */
    private function uploadAttachment($request, $transportationId): void
    {
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/vendor/transportation/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->transportationAttachments::create([
                        "file_id" => $transportationId,
                        "file_name" => $fileName,
                        "file_type" => 'transportation',
                        "file_url" =>  $fileUrl
                    ]);
            }
        }
    }

    /**
     * Retrieve the role record based on requested data.
     *
     *
     * @param object $user
     *
     * @return mixed Returns the role data

     */
    private function getRole($user)
    {
        return $this->role->where('role_name', Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'))
        ->where('company_id', $user['company_id'])
        ->whereNull('deleted_at')
        ->where('status',1)
        ->first('id');
    }

    /**
     * create a new user.
     *
     * @param array $request The request data containing the driver_name, driver_email
     * @param object $role
     * @param object $user
     * @param int $transportationId
     *
     * @return mixed Returns the created user record.
     */
    private function createSupervisorUser($request,$role,$user,$transportationId): mixed
    {
        return $this->authServices->create(
            ['name' => $request['driver_name'],
            'email' => $request['driver_email'],
            'role_id' => $role->id ?? 0,
            'user_id' => $user['id'],
            'status' => 1,
            'password' => Str::random(8),
            'reference_id' => $transportationId,
            'user_type' => Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'),
            'subsidiary_companies' => array(),
            'company_id' => $user['company_id']
        ]);
    }

    /**
     * List the transportation
     *
     * @param $request The request data containing the vendor_id, search_param
     *
     * @return mixed Returns the paginated list of transportation.
     */
    public function list($request): mixed
    {
        $user = $this->getAuthenticatedUser();
        return $this->transportation::with('vendor','transportationAttachments')
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','transportation.vendor_id')
            ->where('vendors.company_id', $user['company_id']);
        })
        ->where(function ($query) use ($request) {
            if (isset($request['vendor_id']) && !empty($request['vendor_id'])) {
                $query->where('vendor_id', '=', $request['vendor_id']);
            }
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('vendor_id', '=', $request['vendor_id'])
                ->where('driver_name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('vehicle_type', 'like', '%' . $request['search_param'] . '%');
            }
        })
        ->select('transportation.*')
        ->orderBy('transportation.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

	 /**
     * Show the transportation data based on the request data
     *
     * @param $request The request data containing the id
     *
     * @return mixed returns the transportation record
     */
    public function show($request) : mixed
    {
        $request = $this->enrichRequestWithUserDetails($request);

        return $this->transportation::with('vendor','transportationAttachments')
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','transportation.vendor_id')
            ->whereIn('vendors.company_id', $request['company_id']);
        })
        ->select('transportation.id', 'transportation.driver_name', 'transportation.driver_contact_number', 'transportation.driver_email', 'transportation.vehicle_type', 'transportation.number_plate', 'transportation.vehicle_capacity', 'transportation.vendor_id', 'transportation.assigned_supervisor', 'transportation.created_by', 'transportation.modified_by', 'transportation.created_at', 'transportation.updated_at', 'transportation.deleted_at')
        ->find($request['id']);
    }

    /**
     * Retrieve the transportation record based on requested data.
     *
     * @param array $request
     * @param object $user
     *
     * @return mixed Returns the transportation data

     */
    private function getTransportation($request, $user)
    {
        return $this->transportation
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','transportation.vendor_id')
            ->where('vendors.company_id', $user['company_id']);
        })
        ->select('transportation.id', 'transportation.driver_name', 'transportation.driver_contact_number', 'transportation.driver_email', 'transportation.vehicle_type', 'transportation.number_plate', 'transportation.vehicle_capacity', 'transportation.vendor_id', 'transportation.assigned_supervisor', 'transportation.created_by', 'transportation.modified_by', 'transportation.created_at', 'transportation.updated_at', 'transportation.deleted_at')
        ->find($request['id']);
    }

	 /**
     * Update the transportation
     *
     * @param $request The request data containing the update transportation data
     *
     * @return mixed  Returns An array of validation errors or boolean based on the processing result
     */
    public function update($request): mixed
    {
        $user = $this->getAuthenticatedUser();
        $data = $this->getTransportation($request, $user);

        if(is_null($data)){
            return [
                "isUpdated" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $request['modified_by'] = $user['id'];

        $this->uploadAttachment($request, $request['id']);

        if(isset($request["assigned_supervisor"]) && $request["assigned_supervisor"] == 1){
            $userData = $this->user->where('email', $request['driver_email'])->get();
            if(isset($userData) && (count($userData) > 0)){
                return  [
                    "isUpdated" => $data->update($request->all()),
                    "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
                ];
            }

            $role = $this->getRole($user);


            if(empty($role)){

                $addRole['name'] = Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR');
                $addRole['company_id'] = $user['company_id'];
                $addRole['special_permission'] = 0;
                $addRole['created_by'] = $user['id'];

                $this->rolesServices->create($addRole);

                $role = $this->getRole($user);

            }

            $res = $this->createSupervisorUser($request,$role,$user,$request['id']);

            if($res){
                return  [
                    "isUpdated" => $data->update($request->all()),
                    "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
                ];
            }

            return [
                "isCreated" => false,
                "message"=> self::MESSAGE_TRANSPORTATION_NOT_CREATED
            ];
        }

        return  [
            "isUpdated" => $data->update($request->all()),
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

	 /**
     * Delete the transportation
     *
     * @param $request The request data containing the id
     *
     * @return mixed  Returns an array with two keys: 'isDeleted' and 'message'
     */
    public function delete($request): mixed
    {

        $user = $this->getAuthenticatedUser();

        $data = $this->getTransportation($request, $user);

        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $data->transportationAttachments()->delete();
        $data->delete();

        $userData = $this->user->where('email', $data['driver_email'])->get();
        if(isset($userData) && (count($userData) > 0)){
            $userInfo = $this->user::find($userData[0]['id']);
            $userInfo->delete();
        }

        return [
            "isDeleted" => true,
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * Delete attachment
     *
     * @param $request  The request data containing the id
     *
     * @return mixed Returns an array with two keys: 'isDeleted' and 'message'
     */
    public function deleteAttachment($request): mixed
    {

        $user = $this->getAuthenticatedUser();
        $data = $this->transportationAttachments
        ->join('transportation', 'transportation.id', 'transportation_attachments.file_id')
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','transportation.vendor_id')
            ->where('vendors.company_id', $user['company_id']);
        })
        ->select('transportation_attachments.id', 'transportation_attachments.file_id', 'transportation_attachments.file_name', 'transportation_attachments.file_type', 'transportation_attachments.file_url', 'transportation_attachments.created_at', 'transportation_attachments.updated_at', 'transportation_attachments.deleted_at', 'transportation_attachments.created_by', 'transportation_attachments.modified_by')
        ->find($request['id']);

        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }
    /**
     * Get the transportation data based on request
     *
     * @param $request The request data containing the vendor_id
     *
     * @return mixed Returns the transportation data
     */
    public function dropdown($request): mixed
    {
        $user = $this->getAuthenticatedUser();
        return $this->transportation
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','transportation.vendor_id')
            ->where('vendors.company_id', $user['company_id']);
        })
        ->where('transportation.vendor_id', '=', $request['vendor_id'])
        ->select('transportation.id', 'transportation.driver_name')
        ->orderBy('transportation.id','DESC')
        ->get();

    }
}
