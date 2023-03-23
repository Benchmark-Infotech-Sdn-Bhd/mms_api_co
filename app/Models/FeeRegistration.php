<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeRegistration extends Model
{
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
        'applicable_for',
        'sectors',
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'item_name' => 'required|regex:/^[a-zA-Z0-9 @&$]*$/u|max:150',
        'cost' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        'fee_type' => 'required',
        'applicable_for' => 'required',
        'sectors' => 'required',
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'item_name' => 'required|regex:/^[a-zA-Z0-9 @&$]*$/u|max:150',
        'cost' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        'fee_type' => 'required',
        'applicable_for' => 'required',
        'sectors' => 'required',
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
