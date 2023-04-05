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
        'tnb_bill_account_Number',
        'water_bill_account_Number',
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'name' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'location' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'maximum_pax_per_unit' => 'required|regex:/^[0-9]*$/',
        'deposit' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        'rent_per_month' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        'tnb_bill_account_Number' => 'required|regex:/^[0-9]*$/u|max:12',
        'water_bill_account_Number' => 'required|regex:/^[0-9]*$/u|max:13',
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'name' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'location' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'maximum_pax_per_unit' => 'required|regex:/^[0-9]*$/',
        'deposit' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        'rent_per_month' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        'tnb_bill_account_Number' => 'required|regex:/^[0-9]*$/u|max:12',
        'water_bill_account_Number' => 'required|regex:/^[0-9]*$/u|max:13',
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
