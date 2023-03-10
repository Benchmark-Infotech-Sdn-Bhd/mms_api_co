<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'type',
        'email_address',
        'contact_number',
        'person_in_charge',
        'pic_contact_number',
        'address',
        'state',
        'city',
        'postcode',
        'attachments',
        'remarks',
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'name' => 'required|max:150',
        'type' => 'required',
        'email_address' => 'required',
        'contact_number' => 'required',
        'person_in_charge' => 'required',
        'pic_contact_number' => 'required',
        'address' => 'required',
        'state' => 'required',
        'city' => 'required',
        'postcode' => 'required',
        'attachments' => 'required',
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