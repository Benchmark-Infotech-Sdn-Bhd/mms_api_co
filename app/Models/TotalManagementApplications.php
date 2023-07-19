<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'crm_prospect_id', 'service_id', 'quota_applied', 'person_incharge', 'cost_quoted', 'status', 'remarks', 'created_by', 'modified_by'
    ];

    /**
     * The attributes that are required.
     * 
     * @return array
     */
    public function rulesForSubmission(): array
    {
        return [
            'id' => 'required|regex:/^[0-9]+$/',
            'quota_requested' => 'required|regex:/^[0-9]+$/|max:3',
            'person_incharge' => 'required',
            'cost_quoted' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
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
    /**
     * @return HasMany
     */
    public function applicationProject(): HasMany
    {
        return $this->hasMany(TotalManagementProject::class, 'application_id');
    }
}
