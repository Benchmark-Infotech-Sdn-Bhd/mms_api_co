<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CRMProspectService extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'crm_prospect_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'crm_prospect_id', 'service_id', 'service_name', 'status'
    ];
}