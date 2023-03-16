<?php

namespace App\Services;
use Illuminate\Support\Facades\Validator;

class ValidationServices
{
    /**
     * The attributes that store validation errors.
     */
    protected $errors;
    /**
     * Validate method for model.
     */
    public function validate($data,$rules){
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
}
