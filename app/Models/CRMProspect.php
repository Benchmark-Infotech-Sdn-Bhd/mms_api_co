<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Services;
use App\Models\LoginCredential;
use App\Models\CRMProspectAttachment;

class CRMProspect extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'crm_prospects';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'company_name', 'roc_number', 'director_or_owner', 'contact_number', 'email', 'address', 'status', 'pic_name', 'pic_contact_number', 'pic_designation', 'registered_by', 'sector_type', 'created_by', 'modified_by'
    ];

    /**
     * @return BelongsToMany
     */
    public function prospectServices(): BelongsToMany
    {
        return $this->belongsToMany(Services::class, 'crm_prospect_services', 'crm_prospect_id', 'service_id');
    }
    /**
     * @return HasMany
     */
    public function prospectLoginCredentials(): HasMany
    {
        return $this->hasMany(LoginCredential::class, 'crm_prospect_id');
    }
    /**
     * @return HasMany
     */
    public function prospectAttachment(): HasMany
    {
        return $this->hasMany(CRMProspectAttachment::class, 'file_id');
    }
}