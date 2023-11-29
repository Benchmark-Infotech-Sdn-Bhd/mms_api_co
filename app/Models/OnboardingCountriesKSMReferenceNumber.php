<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class OnboardingCountriesKSMReferenceNumber extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'onboarding_countries_ksm_reference_number';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'application_id', 'onboarding_country_id', 'ksm_reference_number', 'valid_until', 'quota', 'utilised_quota', 'status', 'created_by', 'modified_by'
    ];
}
