<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'application_id', 'name', 'state', 'city', 'address', 'supervisor_id', 'supervisor_type', 'employee_id', 'transportation_provider_id', 'driver_id', 'assign_as_supervisor', 'annual_leave', 'medical_leave', 'hospitalization_leave', 'valid_until', 'created_by', 'modified_by'
    ];
    /**
     * @return BelongsTo
     */
    public function totalManagementApplications()
    {
        return $this->belongsTo(TotalManagementApplications::class);
    }

    /**
     * @return HasOne
     */
    public function projectAttachments()
    {
        return $this->hasOne(TotalManagementProjectAttachments::class, 'file_id');
    }
}
