<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectRecruitmentCallingVisaApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'direct_recruitment_calling_visa_approval';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'worker_id', 'status', 'calling_visa_generated', 'calling_visa_valid_until', 'remarks', 'created_by', 'modified_by'
    ];
    /**
     * The function returns array that are required for search.
     * 
     * @return array
     */
    public function rulesForSearch(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
}
