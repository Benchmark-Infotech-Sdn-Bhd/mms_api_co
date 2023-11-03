<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolePermission extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'role_permission';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'role_id', 'module_id', 'permission_id', 'created_by', 'modified_by'
    ];
    /**
     * @return HasMany
    */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'id', 'permission_id');
    }
    /**
     * @return HasMany
    */
    public function modules()
    {
        return $this->hasMany(Module::class, 'id', 'module_id');
    }
}
