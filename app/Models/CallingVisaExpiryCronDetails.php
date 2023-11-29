<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class CallingVisaExpiryCronDetails extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'calling_visa_expiry_cron_details';
    protected $fillable = ['application_id', 'onboarding_country_id', 'ksm_reference_number', 'approved_quota', 'initial_utilised_quota', 'current_utilised_quota'];
}
