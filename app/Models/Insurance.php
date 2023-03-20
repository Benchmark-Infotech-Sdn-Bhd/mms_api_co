<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insurance extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'insurance';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'no_of_worker_from',
        'no_of_worker_to',
        'fee_per_pax',
        'vendor_id',
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'no_of_worker_from' => 'required',
        'no_of_worker_to' => 'required',
        'fee_per_pax' => 'required',
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
     * @return belongsTo
     */
    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor');
    }
}
