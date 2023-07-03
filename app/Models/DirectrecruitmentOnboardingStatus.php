<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DirectrecruitmentOnboardingStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'directrecruitment_onboarding_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name', 'status'];
}