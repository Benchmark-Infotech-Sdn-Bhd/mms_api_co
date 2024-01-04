<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingAttestation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'onboarding_attestation';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'application_id', 'onboarding_country_id', 'onboarding_agent_id', 'ksm_reference_number', 'submission_date', 'collection_date', 'item_name', 'status', 'file_url ', 'remarks', 'created_by', 'modified_by'
    ];

}
