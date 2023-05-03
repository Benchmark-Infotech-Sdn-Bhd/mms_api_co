<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CRMProspect extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'crm_prospects';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'company_name', 'roc_number', 'director_or_owner', 'contact_number', 'email', 'address', 'status', 'pic_name', 'pic_contact_number', 'pic_designation', 'registered_by', 'created_by', 'modified_by'
    ];
    /**
     * @return HasMany
     */
    public function prospectServices(): HasMany
    {
        return $this->hasMany(CRMProspectService::class, 'crm_prospect_id');
    }
    /**
     * @return HasMany
     */
    public function prospectLoginCredentials(): HasMany
    {
        return $this->hasMany(LoginCredential::class, 'crm_prospect_id');
    }
}