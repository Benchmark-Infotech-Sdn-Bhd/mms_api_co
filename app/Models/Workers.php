<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workers extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'workers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['onboarding_country_id','agent_id','application_id','name','gender', 'date_of_birth', 'passport_number', 'passport_valid_until', 'fomema_valid_until','address', 'status', 'cancel_status', 'remarks','city','state', 'special_pass', 'special_pass_submission_date', 'special_pass_valid_until', 'plks_status', 'plks_expiry_date', 'directrecruitment_status', 'created_by','modified_by', 'crm_prospect_id', 'total_management_status', 'econtract_status', 'module_type'];

   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
        'date_of_birth' => 'required|date_format:Y-m-d',
        'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
        'passport_number' => 'required|regex:/^[a-zA-Z0-9]*$/|unique:workers',
        'passport_valid_until' => 'required|date_format:Y-m-d',
        'address' => 'required',
        'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
        'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150'
    ];
    /**
     * The function returns array that are required for updation.
     * @param $params
     * @return array
     */
    public function rulesForUpdation($id): array
    {
        // Unique name with deleted at
        return [
            'id' => 'required|regex:/^[0-9]+$/',
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
            'passport_number' => 'required|regex:/^[a-zA-Z0-9]*$/|unique:workers,passport_number,'.$id,
            'passport_valid_until' => 'required|date_format:Y-m-d',
            'address' => 'required',
            'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150'
        ];
    }

    /**
     * @return HasMany
     */
    public function workerAttachments()
    {
        return $this->hasMany(WorkerAttachments::class, 'file_id')->where('file_type', '<>', 'WORKERATTACHMENT');
    }

    /**
     * @return HasOne
     */
    public function workerKin()
    {
        return $this->hasOne(WorkerKin::class, 'worker_id');
    }

    /**
     * @return HasOne
     */
    public function workerVisa()
    {
        return $this->hasOne(WorkerVisa::class, 'worker_id');
    }

    /**
     * @return HasOne
     */
    public function workerBioMedical()
    {
        return $this->hasOne(WorkerBioMedical::class, 'worker_id');
    }

    /**
     * @return HasOne
     */
    public function workerFomema()
    {
        return $this->hasOne(WorkerFomema::class, 'worker_id');
    }
    /**
     * @return HasMany
     */
    public function workerFomemaAttachments()
    {
        return $this->hasMany(FOMEMAAttachment::class, 'file_id');
    }

    /**
     * @return HasOne
     */
    public function workerInsuranceDetails()
    {
        return $this->hasOne(WorkerInsuranceDetails::class, 'worker_id');
    }

    /**
     * @return HasOne
     */
    public function workerBankDetails()
    {
        return $this->hasOne(WorkerBankDetails::class, 'worker_id');
    }

    /**
     * @return HasMany
     */
    public function workerInsuranceAttachments(): HasMany
    {
        return $this->hasMany(WorkerInsuranceAttachments::class, 'file_id');
    }

    /**
     * @return HasMany
     */
    public function workerEmployment(): HasMany
    {
        return $this->hasMany(WorkerEmployment::class, 'worker_id');
    }

        /**
     * @return HasMany
     */
    public function directrecruitmentWorkers(): HasMany
    {
        return $this->hasMany(DirectrecruitmentWorkers::class, 'worker_id');
    }

     /**
     * @return HasMany
     */
    public function workerOtherAttachments(): HasMany
    {
        return $this->hasMany(WorkerAttachments::class, 'file_id')->where('file_type', '=', 'WORKERATTACHMENT');
    }
    /**
     * @return hasOne
     */
    public function SpecialPassAttachments()
    {
        return $this->hasOne(SpecialPassAttachments::class, 'file_id');
    }
    /**
     * @return hasOne
     */
    public function WorkerRepatriationAttachments()
    {
        return $this->hasOne(WorkerRepatriationAttachments::class, 'file_id');
    }
    /**
     * @return hasOne
     */
    public function WorkerPLKSAttachments()
    {
        return $this->hasOne(WorkerPLKSAttachments::class, 'file_id');
    }
    /**
     * @return hasOne
     */
    public function CancellationAttachment()
    {
        return $this->hasOne(CancellationAttachment::class, 'file_id');
    }
    /**
     * @return hasOne
     */
    public function WorkerImmigrationAttachments()
    {
        return $this->hasOne(WorkerImmigrationAttachments::class, 'file_id');
    }
}
