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
use Illuminate\Support\Str;

class TransportationServices
{
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
     * @var RolesServices
     */
    private RolesServices $rolesServices;

    public function __construct(Transportation $transportation, TransportationAttachments $transportationAttachments, Storage $storage, AuthServices $authServices, Role $role, User $user, RolesServices $rolesServices)
    {
        $this->transportation = $transportation;
        $this->transportationAttachments = $transportationAttachments;
        $this->storage = $storage;
        $this->authServices = $authServices;
        $this->role = $role;
        $this->user = $user;
        $this->rolesServices = $rolesServices;
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
       if(!($this->transportation->validate($request->all()))){
           return $this->transportation->errors();
       }
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->transportation->validateUpdation($request->all()))){
            return $this->transportation->errors();
        }
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {   
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $transportationData = $this->transportation::create([
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
        $transportationId = $transportationData->id;
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

        if(isset($request["assigned_supervisor"]) && $request["assigned_supervisor"] == 1){
            
            $role = $this->role->where('role_name', Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'))
                ->where('company_id', $user['company_id'])
                ->whereNull('deleted_at')
                ->where('status',1)
                ->first('id'); 

            if(empty($role)){

                $addRole['name'] = Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR');
                $addRole['company_id'] = $user['company_id'];
                $addRole['special_permission'] = 0;
                $addRole['created_by'] = $user['id'];

                $this->rolesServices->create($addRole);

                $role = $this->role->where('role_name', Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'))
                ->where('company_id', $user['company_id'])
                ->whereNull('deleted_at')
                ->where('status',1)
                ->first('id'); 
                
            }

            $res = $this->authServices->create(
                ['name' => $request['driver_name'],
                'email' => $request['driver_email'],
                'role_id' => array($role->id) ?? [],
                'user_id' => $user['id'],
                'status' => 1,
                'password' => Str::random(8),
                'reference_id' => $transportationId,
                'user_type' => Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'),
                'subsidiary_companies' => array(),
                'company_id' => $user['company_id']
            ]);

            if($res){
                return $transportationData;
            }
            
            $data = $this->transportation::findorfail($transportationData->id);
            $data->transportationAttachments()->delete();
            $transportationData->delete();

            return [
                "isCreated" => false,
                "message"=> "Transportation data not created"
            ];
        }

        return $transportationData;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->transportation::with('vendor','transportationAttachments')
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
        ->orderBy('transportation.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

	 /**
     *
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        return $this->transportation::with('vendor','transportationAttachments')->findorfail($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {     
        $data = $this->transportation::find($request['id']);
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/vendor/transportation/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->transportationAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'transportation',
                        "file_url" =>  $fileUrl          
                    ]);  
            }
        }
        if(is_null($data)){
            return [
                "isUpdated" => false,
                "message" => "Data not found"
            ];
        }

        if(isset($request["assigned_supervisor"]) && $request["assigned_supervisor"] == 1){
            $userData = $this->user->where('email', $request['driver_email'])->get();
            if(isset($userData) && (count($userData) > 0)){
                return  [
                    "isUpdated" => $data->update($request->all()),
                    "message" => "Updated Successfully"
                ];
            }
            $role = $this->role->where('role_name', Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'))
                ->where('company_id', $user['company_id'])
                ->whereNull('deleted_at')
                ->where('status',1)
                ->first('id');

            if(empty($role)){

                $addRole['name'] = Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR');
                $addRole['company_id'] = $user['company_id'];
                $addRole['special_permission'] = 0;
                $addRole['created_by'] = $user['id'];

                $this->rolesServices->create($addRole);

                $role = $this->role->where('role_name', Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'))
                ->where('company_id', $user['company_id'])
                ->whereNull('deleted_at')
                ->where('status',1)
                ->first('id'); 
                
            }

            $res = $this->authServices->create(
                ['name' => $request['driver_name'],
                'email' => $request['driver_email'],
                'role_id' => array($role->id) ?? [],
                'user_id' => $user['id'],
                'status' => 1,
                'password' => Str::random(8),
                'reference_id' => $request['id'],
                'user_type' => Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'),
                'subsidiary_companies' => array(),
                'company_id' => $user['company_id']
            ]);

            if($res){
                return  [
                    "isUpdated" => $data->update($request->all()),
                    "message" => "Updated Successfully"
                ];
            }
            
            return [
                "isCreated" => false,
                "message"=> "Transportation data not created"
            ];
        }

        return  [
            "isUpdated" => $data->update($request->all()),
            "message" => "Updated Successfully"
        ];
    }
	 /**
     *
     * @param $request
     * @return mixed
     */    
    public function delete($request): mixed
    {     
        $data = $this->transportation::find($request['id']);

        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
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
            "message" => "Deleted Successfully"
        ];
    }

    /**
     *
     * @param $request
     * @return mixed
     */    
    public function deleteAttachment($request): mixed
    {   
        $data = $this->transportationAttachments::find($request['id']); 
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function dropdown($request): mixed
    {
        return $this->transportation
        ->where('vendor_id', '=', $request['vendor_id'])
        ->select('id', 'driver_name')
        ->orderBy('id','DESC')
        ->get();
    }
}