<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CRMProspectSector extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'crm_prospect_sector';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'crm_prospect_id', 'service_id', 'service_name', 'sector_type', 'contract_type', 'created_by', 'modified_by'
    ];
    /**
     * @return HasMany
     */
    public function prospectSectorAttachment(): HasMany
    {
        return $this->hasMany(CRMProspectAttachment::class, 'prospect_sector_id');
    }
}