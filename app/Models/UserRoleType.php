<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRoleType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'user_role_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'role_id', 'status', 'created_by', 'modified_by'
    ];
    /**
     * @return BelongsTo
    */
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * @return BelongsTo
    */
    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}