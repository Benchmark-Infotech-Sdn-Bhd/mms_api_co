<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Config;

class RolesServices
{
    /**
     * @var Role
     */
    private Role $role;
    /**
     * @var Company
     */
    private Company $company;

    /**
     * RolesServices constructor.
     * @param Role $role
     * @param Company $company
     */
    public function __construct(Role $role, Company $company)
    {
        $this->role = $role;
        $this->company = $company;
    }

    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:250',
        ];
    }

    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:250'
        ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->role
            ->whereIn('company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('role_name', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('id', 'role_name', 'status')
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->role->findOrFail($request['id']);
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function create($request): bool|array
    {
        if ($request['name'] == 'Admin' || $request['name'] == 'admin') {
            return [
                'adminError' => true
            ];
        }
        if ($request['special_permission'] == 1) {
            if($request['user_type'] != 'Admin') {
                return [
                    'adminUserError' => true
                ];
            }
            $companyDetail = $this->company->findOrFail($request['company_id']);
            if($companyDetail->parent_id != 0) {
                return [
                    'subsidiaryError' => true
                ];
            }
        }
        
        $this->role->create([
            'role_name'     => $request['name'] ?? '',
            'system_role'   => $request['system_role'] ?? 0,
            'status'        => $request['status'] ?? 1,
            'parent_id'     => $request['parent_id'] ?? 0,
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0,
            'company_id'   => $request['company_id'] ?? 0,
            'special_permission' => $request['special_permission'] ?? 0
        ]);
        return true;
    }

    /**
     * @param $request
     * @return bool
     */
    public function update($request): bool
    {
        $role = $this->role->findOrFail($request['id']);
        $role->role_name    = $request['name'] ?? $role->role_name;
        $role->system_role  = $request['system_role'] ?? $role->system_role;
        $role->status       = $request['status'] ?? $role->status;
        $role->parent_id    = $request['parent_id'] ?? $role->parent_id;
        $role->modified_by  = $request['modified_by'] ?? $role->modified_by;
        $role->save();
        return true;
    }

    /**
     * @param $request
     * @return bool
     */
    public function delete($request): bool
    {
        return $this->role->where('id', $request['id'])->delete();
    }

    /**
     * @param $companyId
     * @return mixed
     */
    public function dropDown($companyId): mixed
    {
        return $this->role->where('status', 1)
            ->whereIn('company_id', $companyId)
            ->select('id', 'role_name', 'special_permission')
            ->get();
    }
    /**
     * @param $request
     * @return array
     */
    public function updateStatus($request) : array
    {
        $role = $this->role->find($request['id']);
        if(is_null($role)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $role->status = $request['status'];
        $role->modified_by = $request['modified_by'];
        return  [
            "isUpdated" => $role->save() == 1,
            "message" => "Updated Successfully"
        ];
    }
}
