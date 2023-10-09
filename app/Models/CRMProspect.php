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
        'company_name', 'roc_number', 'director_or_owner', 'contact_number', 'email', 'address', 'status', 'pic_name', 'pic_contact_number', 'pic_designation', 'registered_by','bank_account_number','bank_account_name','tax_id','account_receivable_tax_type','account_payable_tax_type','xero_contact_id', 'created_by', 'modified_by', 'company_id'
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
    /**
     * @return HasMany
     */
    public function directrecruitmentApplications(): HasMany
    {
        return $this->hasMany(DirectrecruitmentApplications::class, 'crm_prospect_id');
    }
    /**
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}