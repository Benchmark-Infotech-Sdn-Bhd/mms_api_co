<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RenewalNotification extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'renewal_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'notification_name', 'status'
    ];
}
