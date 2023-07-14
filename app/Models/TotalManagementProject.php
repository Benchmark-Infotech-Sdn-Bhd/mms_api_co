<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class TotalManagementProject extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'total_management_project';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'application_id', 'name', 'state', 'city', 'address', 'employee_id', 'transportation_provider_id', 'driver_id', 'assign_as_supervisor', 'annual_leave', 'medical_leave', 'hospitalization_leave', 'created_by', 'modified_by'
    ];

}
