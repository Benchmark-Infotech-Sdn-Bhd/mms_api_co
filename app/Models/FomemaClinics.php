<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class FomemaClinics extends Model
{
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
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'clinic_name' => 'required',
        'person_in_charge' => 'required',
        'pic_contact_number' => 'required',
        'address' => 'required',
        'state' => 'required',
        'city' => 'required',
        'postcode' => 'required',
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
    
    // Returns Validation errors
    public function errors()
    {
        return $this->errors;
    }

}
