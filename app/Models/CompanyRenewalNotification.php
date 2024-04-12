<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CompanyRenewalNotification extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'company_renewal_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'notification_id', 'company_id', 'renewal_notification_status', 'renewal_duration_in_days', 'renewal_frequency_cycle', 'expired_notification_status', 'expired_duration_in_days', 'expired_frequency_cycle', 'created_by', 'modified_by'
    ];
}
