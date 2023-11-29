<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectRecruitmentOnboardingCountry extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'directrecruitment_onboarding_countries';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'application_id', 'country_id', 'quota', 'utilised_quota', 'status', 'onboarding_status', 'created_by', 'modified_by'
    ];
    /**
     * @return HasMany
     */
    public function onboardingKSMReferenceNumbers()
    {
        return $this->hasMany(OnboardingCountriesKSMReferenceNumber::class, 'onboarding_country_id');
    }
}
