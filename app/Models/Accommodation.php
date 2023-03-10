<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accommodation extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accommodation';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'location',
        'square_feet',
        'accommodation_name',
        'maximum_pax_per_room',
        'cost_per_pax',
        'attachment',
        'deposit',
        'rent_per_month',
        'vendor_id',
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'name' => 'required',
        'location' => 'required',
        'square_feet' => 'required',
        'accommodation_name' => 'required',
        'maximum_pax_per_room' => 'required',
        'cost_per_pax' => 'required',
        'attachment' => 'required',
        'deposit' => 'required',
        'rent_per_month' => 'required',
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
     * @return BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor');
    }
}
