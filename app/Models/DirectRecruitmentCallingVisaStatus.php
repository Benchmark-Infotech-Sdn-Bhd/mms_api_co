<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectRecruitmentCallingVisaStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'direct_recruitment_calling_visa_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'application_id', 'onboarding_country_id', 'agent_id', 'item', 'updated_on', 'created_by', 'modified_by'
    ];
    /**
     * @return HasMany
     */
    public function callingVisa(): HasMany
    {
        return $this->hasMany(DirectRecruitmentCallingVisa::class, 'calling_visa_status_id');
    }
}
