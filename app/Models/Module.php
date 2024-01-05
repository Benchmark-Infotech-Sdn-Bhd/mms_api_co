<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'modules';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'module_name', 'module_url', 'parent_id', 'order_id', 'status', 'feature_flag', 'created_by', 'modified_by'
    ];

    /**
     * @return BelongsToMany
     */
    public function RoleModules(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'module_id', 'role_id');
    }
}
