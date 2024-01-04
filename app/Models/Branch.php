<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Branch extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'branch';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['branch_name','state','city','branch_address','postcode',
    'remarks','created_by','modified_by', 'company_id'];
    /**
     * @return HasMany
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'branch_name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
        'state' => 'required|max:150',
        'city' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
        'branch_address' => 'required',
        'postcode' => 'required|regex:/^[0-9]+$/|max:5'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'branch_name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
        'state' => 'required|max:150',
        'city' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
        'branch_address' => 'required',
        'postcode' => 'required|regex:/^[0-9]+$/|max:5'
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

    /**
     * Validate method for model.
     */
    public function validateStatus($data,$rules){
        // make a new validator object
        $validator = Validator::make($data,$rules);
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
    public function branchServices()
    {
        return $this->hasMany('App\Models\BranchesServices', 'branch_id');
    }
}
