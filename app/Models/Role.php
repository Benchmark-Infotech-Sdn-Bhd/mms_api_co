<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'role_name', 'system_role', 'status', 'parent_id', 'created_by', 'modified_by', 'company_id', 'special_permission'
    ];

    /**
     * @return BelongsToMany
     */
    public function RoleModules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'role_permission', 'role_id', 'module_id');
    }
    /**
     * @return HasMany
     */
    public function userRoleTypes()
    {
        return $this->hasMany(UserRoleType::class, 'role_id');
    }
    /**
     * @return BelongsTo
     */
    public function roleCompany()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}