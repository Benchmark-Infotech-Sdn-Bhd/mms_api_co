<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Agent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agent';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['agent_name','country','city','person_in_charge','pic_contact_number',
    'email_address','company_address','created_by','modified_by'];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'agent_name' => 'required|max:250',
        'country' => 'required|max:150',
        'city' => 'max:150',
        'person_in_charge' => 'required|max:255',
        'pic_contact_number' => 'required|max:20',
        'email_address' => 'required|email',
        'company_address' => 'required'
    ];
    /**
     * The attributes that store validation errors.
     */
    protected $errors;
    /**
     * Validate method for model.
     */
    public function validate($data){
        // make a new validator object
        $validator = Validator::make($data,$this->rules);
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
