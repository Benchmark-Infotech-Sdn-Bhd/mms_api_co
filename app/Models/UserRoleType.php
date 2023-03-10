<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserRoleType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'user_role_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'role_id', 'status', 'created_by', 'modified_by'
    ];
}