<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'maximum_pax_per_unit',
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
        'maximum_pax_per_unit' => 'required',
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
    /**
     * @return hasMany
     */
    public function accommodationAttachments()
    {
        return $this->hasMany('App\Models\AccommodationAttachments', 'file_id');
    }
}
