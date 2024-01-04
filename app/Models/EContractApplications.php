<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EContractApplications extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'e-contract_applications';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'crm_prospect_id', 'service_id', 'person_incharge', 'quota_requested', 'cost_quoted', 'status', 'remarks', 'created_by', 'modified_by', 'company_id'
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
            'cost_quoted' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    
    /**
     * @return HasMany
     */
    public function applicationAttachment(): HasMany
    {
        return $this->hasMany(EContractApplicationAttachments::class, 'file_id');
    }
}
