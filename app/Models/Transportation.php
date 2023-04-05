<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'driver_name' => 'required|regex:/^[a-zA-Z @&$]*$/u|max:150',
        'driver_contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:11',
        'vehicle_type' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'number_plate' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
        'vehicle_capacity' => 'required|regex:/^[0-9]*$/',
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'driver_name' => 'required|regex:/^[a-zA-Z @&$]*$/u|max:150',
        'driver_contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:11',
        'vehicle_type' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'number_plate' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
        'vehicle_capacity' => 'required|regex:/^[0-9]*$/',
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
     * @return belongsTo
     */
    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor');
    }
    /**
     * @return hasMany
     */
    public function transportationAttachments()
    {
        return $this->hasMany('App\Models\TransportationAttachments', 'file_id');
    }
}
