<?php

namespace App\Services;
use App\Models\User;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;

class UserServices
{
	private User $user;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    /**
     * UserServices constructor.
     * @param User $user
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     */
    public function __construct(User $user,ValidationServices $validationServices,
    AuthServices $authServices)
    {
        $this->user = $user;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function adminList($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return $this->user
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('users.name', 'like', "%{$request['search_param']}%")
                ->orWhere('users.email', 'like', '%'.$request['search_param'].'%');
            }
        })
        ->where('users.user_type', Config::get('services.ROLE_TYPE_ADMIN'))
        ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.user_type', 'users.status')
        ->distinct()
        ->orderBy('users.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function adminShow($request) : mixed
    {
    	if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $user = $this->user->find($request['id']);        
        return $user;
    }

    /**
     * @param $request
     * @return bool
     */
    public function adminUpdate($request)
    {
    	if(!($this->validationServices->validate($request,['id' => 'required','name' => 'required','email' => 'required|email|max:150|unique:users,email,'.$request['id'].',id,deleted_at,NULL']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $user = $this->user->where('id',$request['id'])->first();
        
        $user->update([
            'name' => $request['name'] ?? $user['name'],
            'email' => $request['email'] ?? $user['email'],
            'modified_by' => $request['user_id'] ?? $user['modified_by']
        ]);

        return  [
            "isUpdated" => true,
            "message" => "Updated Successfully"
        ];
    }

    /**
     * @param $request
     * @return array
     */
    public function adminUpdateStatus($request) : array
    {
        if(!($this->validationServices->validate($request,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $user = $this->user->find($request['id']);
        if(is_null($user)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $user->status = $request['status'];
        return  [
            "isUpdated" => $user->save() == 1,
            "message" => "Updated Successfully"
        ];
    }
}
