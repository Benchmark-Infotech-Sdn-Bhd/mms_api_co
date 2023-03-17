<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Config;

class RolesServices
{
    /**
     * @var Role
     */
    private Role $role;

    /**
     * RolesServices constructor.
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'name' => 'required|max:250',
        ];
    }

    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|max:250'
        ];
    }

    /**
     * @return mixed
     */
    public function list() 
    {
        return $this->role->where('status', 1)
            ->select('id', 'role_name', 'status')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function show($request) 
    {
        return $this->role->findOrFail($request['id']);
    }

    /**
     * @param $request
     * @return bool
     */
    public function create($request)
    {
        $role = $this->role->create([
            'role_name'     => $request['name'] ?? '',
            'system_role'   => $request['system_role'] ?? 0,
            'status'        => $request['status'] ?? 1,
            'parent_id'     => $request['parent_id'] ?? 0,
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0
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
    public function updateStatus($request): bool
    {
        $role = $this->role->findOrFail($request['id']);
        $role->status = $request['status'] ?? $role->status;
        $role->save();
        return true;
    }

    /**
     * @return mixed
     */
    public function dropDown(): mixed
    {
        return $this->role->where('status', 1)
            ->select('id', 'role_name')
            ->get();
    }
}
