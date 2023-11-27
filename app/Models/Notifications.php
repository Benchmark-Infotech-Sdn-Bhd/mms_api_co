<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Notifications extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'notifications';
    protected $fillable = ['user_id', 'from_user_id', 'type', 'title', 'message', 'status', 'read_flag', 'created_by', 'modified_by'];
}