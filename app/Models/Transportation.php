<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transportation extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transportation';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'driver_name',
        'driver_contact_number',
        'driver_license_number',
        'vehicle_type',
        'number_plate',
        'vehicle_capacity',
        'vendor_id',
    ];

    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'driver_name' => 'required',
        'driver_contact_number' => 'required',
        'driver_license_number' => 'required',
        'vehicle_type' => 'required',
        'number_plate' => 'required',
        'vehicle_capacity' => 'required',
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
