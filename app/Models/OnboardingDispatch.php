<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingDispatch extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'onboarding_dispatch';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'onboarding_attestation_id', 'date', 'time', 'reference_number', 'employee_id', 'from', 'calltime', 'area', 'employer_name', 'phone_number', 'remarks', 'created_by', 'modified_by', 'dispatch_status', 'job_type', 'passport', 'document_name', 'payment_amount', 'worker_name', 'acknowledgement_remarks', 'acknowledgement_date'
    ];

    /**
     * @return HasMany
     */
    public function dispatchAttachments(): HasMany
    {
        return $this->hasMany(OnboardingDispatchAttachments::class, 'file_id');
    }

}
