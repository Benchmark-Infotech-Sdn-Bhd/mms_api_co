<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class XeroSettings extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'xero_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title', 'remarks', 'url', 'client_id', 'client_secret', 'tenant_id', 'access_token', 'refresh_token', 'created_by', 'modified_by'
    ];
}
