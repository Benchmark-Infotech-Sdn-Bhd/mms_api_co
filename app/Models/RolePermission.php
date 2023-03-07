<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RolePermission extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'role_permission';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'role_id', 'module_id', 'permission_id', 'created_by', 'modified_by'
    ];
}
