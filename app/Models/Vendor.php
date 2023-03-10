<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vendors';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'state',
        'type',
        'person_in_charge',
        'contact_number',
        'email_address',
        'address',

    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'name' => 'required|max:150',
        'state' => 'required',
        'type' => 'required',
        'person_in_charge' => 'required',
        'contact_number' => 'required',
        'email_address' => 'required',
        'address' => 'required',
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

    /**
     * @return hasMany
     */   
    public function accommodations()
    {
        return $this->hasMany('App\Models\Accommodation');
    }
    /**
     * @return hasMany
     */
    public function insurances()
    {
        return $this->hasMany('App\Models\Insurance');
    }
    /**
     * @return hasMany
     */
    public function transportations()
    {
        return $this->hasMany('App\Models\Transportation');
    }
}