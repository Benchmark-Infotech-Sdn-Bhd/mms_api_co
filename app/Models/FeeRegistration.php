<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class FeeRegistration extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fee_registration';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_name',
        'cost',
        'fee_type',
        'created_by',
        'modified_by',
        'company_id'
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'item_name' => 'required|regex:/^[a-zA-Z0-9 @&$]*$/u|max:150|unique:fee_registration,item_name,NULL,id,deleted_at,NULL',
        'cost' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/|max:9',
        'fee_type' => 'required',
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
            'id' => 'required',
            'item_name' => 'required|regex:/^[a-zA-Z0-9 @&$]*$/u|max:150|unique:fee_registration,item_name,'.$id.',id,deleted_at,NULL',
            'cost' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/|max:9',
            'fee_type' => 'required',
        ];
    }

    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForTypeStandard = [
        'cost' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
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
        $validator = Validator::make($input,$this->rulesForUpdation($input['id']));
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
    public function validateStandardUpdation($input){
        // make a new validator object
        $validator = Validator::make($input,$this->rulesForTypeStandard);
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
     * @return hasMany
     */
    public function feeRegistrationServices()
    {
        return $this->hasMany('App\Models\FeeRegServices', 'fee_reg_id');
    }
    /**
     * @return hasMany
     */
    public function feeRegistrationSectors()
    {
        return $this->hasMany('App\Models\FeeRegSectors', 'fee_reg_id');
    }
    /**
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
