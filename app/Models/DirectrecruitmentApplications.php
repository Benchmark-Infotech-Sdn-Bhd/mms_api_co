<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectrecruitmentApplications extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'directrecruitment_applications';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'crm_prospect_id', 'service_id', 'quota_applied', 'person_incharge', 'cost_quoted', 'service_type', 'remarks', 'status', 'created_by', 'modified_by'
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'crm_prospect_id' => 'required',
        'quota_applied' => 'required|regex:/^[0-9]*$/u|max:3',
        'person_incharge' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'cost_quoted' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        'attachment.*' => 'mimes:jpeg,pdf,png',
    ];
    /**
     * The attributes that are required for updation.
     * @return array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'crm_prospect_id' => 'required',
        'quota_applied' => 'required|regex:/^[0-9]*$/u|max:150',
        'person_incharge' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'cost_quoted' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        'attachment.*' => 'mimes:jpeg,pdf,png',
    ];
    /**
     * The attributes that store validation errors.
     */
    protected $errors;
    /**
     * Validate method for model.
     */
    public function validate($input){
        // make a new validator object
        $validator = Validator::make($input,$this->rules);
        // check for failure
        if($validator->fails()){
            // set errors and return false
            $this->errors = $validator->errors();
            return false;
        }
        // validation pass
        return true;
    }
    /**
     * Validate method for model.
     */
    public function validateUpdation($input){
        // make a new validator object
        $validator = Validator::make($input,$this->rulesForUpdation);
        // check for failure
        if($validator->fails()){
            // set errors and return false
            $this->errors = $validator->errors();
            return false;
        }
        // validation pass
        return true;
    }
    // Returns Validation errors
    public function errors()
    {
        return $this->errors;
    }
    /**
     * @return HasMany
     */
    public function applicationAttachment(): HasMany
    {
        return $this->hasMany(DirectrecruitmentApplicationAttachments::class, 'file_id');
    }
    /**
     * @return BelongsTo
     */
    public function crmProspect()
    {
        return $this->belongsTo(CRMProspect::class);
    }
    /**
     * @return BelongsTo
     */
    public function crmProspectServices()
    {
        return $this->belongsTo(CRMProspectService::class, 'service_id');
    }
}