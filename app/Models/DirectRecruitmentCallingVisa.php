<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectRecruitmentCallingVisa extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'direct_recruitment_calling_visa';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'application_id', 'onboarding_country_id', 'agent_id', 'worker_id', 'calling_visa_status_id', 'calling_visa_reference_number', 'submitted_on', 'status', 'created_by', 'modified_by'
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'calling_visa_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/',
        'submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow'
    ];
    /**
     * The function returns array that are required for updation.
     * 
     * @return array
     */
    public function rulesForUpdation(): array
    {
        return [
            'calling_visa_status_id' => 'required',
            'calling_visa_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/',
            'submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow',
        ];
    }
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
