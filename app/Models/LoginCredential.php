<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class LoginCredential extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'login_credentials';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'crm_prospect_id', 'system_id', 'system_name', 'username', 'password'
    ];
}
