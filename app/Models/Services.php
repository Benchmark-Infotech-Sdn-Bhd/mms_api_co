<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Services extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'services';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'service_name', 'status', 'created_by', 'modified_by'
    ];
}
