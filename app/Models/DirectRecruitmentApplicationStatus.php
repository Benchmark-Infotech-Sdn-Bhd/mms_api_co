<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DirectRecruitmentApplicationStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'direct_recruitment_application_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['status_name', 'status'];
}