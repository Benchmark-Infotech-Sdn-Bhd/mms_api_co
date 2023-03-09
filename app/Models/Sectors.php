<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Sectors extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sectors';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sector_name','sub_sector_name','created_by','modified_by'];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'sector_name' => 'required|max:255',
        'sub_sector_name' => 'max|255'
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
