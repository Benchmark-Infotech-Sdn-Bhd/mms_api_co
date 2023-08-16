<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CRMProspectService extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'crm_prospect_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'crm_prospect_id', 'service_id', 'service_name', 'sector_id', 'sector_name', 'contract_type', 'status', 'from_existing', 'client_quota', 'fomnext_quota', 'initial_quota', 'service_quota', 'air_ticket_deposit'
    ];
    /**
     * @return HasMany
     */
    public function prospectAttachment(): HasMany
    {
        return $this->hasMany(CRMProspectAttachment::class, 'prospect_service_id');
    }
}