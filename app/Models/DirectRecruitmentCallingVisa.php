<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectRecruitmentCallingVisa extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'direct_recruitment_calling_visa_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'application_id', 'onboarding_country_id', 'agent_id', 'worker_id', 'calling_visa_status_id', 'calling_visa_reference_number', 'submitted_on', 'status', 'created_by', 'modified_by'
    ];
}
