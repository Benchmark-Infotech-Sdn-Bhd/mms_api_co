<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FomemaClinics extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fomema_clinics';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'clinic_name',
        'person_in_charge',
        'pic_contact_number',
        'address',
        'state',
        'city',
        'postcode',
        'created_by',
        'modified_by',
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'clinic_name' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'person_in_charge' => 'required|regex:/^[a-zA-Z @&$]*$/u|max:150',
        'pic_contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:11',
        'address' => 'required',
        'state' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
        'city' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
        'postcode' => 'required|regex:/^[0-9]*$/|max:5',
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'clinic_name' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'person_in_charge' => 'required|regex:/^[a-zA-Z @&$]*$/u|max:150',
        'pic_contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:11',
        'address' => 'required',
        'state' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
        'city' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
        'postcode' => 'required|regex:/^[0-9]*$/|max:5',
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

}
