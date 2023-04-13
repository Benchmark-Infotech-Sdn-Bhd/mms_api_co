<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SystemType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'system_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'system_name', 'status'
    ];
}
