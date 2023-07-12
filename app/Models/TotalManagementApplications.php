<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TotalManagementApplications extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'total_management_applications';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'crm_prospect_id', 'service_id', 'quota_applied', 'person_incharge', 'cost_quoted', 'status', 'service_type', 'remarks', 'created_by', 'modified_by'
    ];
    /**
     * @return BelongsTo
     */
    public function crmProspect()
    {
        return $this->belongsTo(CRMProspect::class);
    }
    /**
     * @return BelongsTo
     */
    public function crmProspectServices()
    {
        return $this->belongsTo(CRMProspectService::class, 'service_id');
    }
    /**
     * @return HasMany
     */
    public function applicationAttachment(): HasMany
    {
        return $this->hasMany(TotalManagementApplicationAttachments::class, 'file_id');
    }
}
