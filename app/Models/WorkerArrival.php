<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkerArrival extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'worker_arrival';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'arrival_id', 'worker_id', 'arrival_status', 'arrived_date', 'entry_visa_valid_until', 'jtk_submitted_on', 'remarks', 'created_by', 'modified_by'
    ];
}
