<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Vendor extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vendors';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'email_address',
        'contact_number',
        'person_in_charge',
        'pic_contact_number',
        'address',
        'state',
        'city',
        'postcode',
        'remarks',
        'created_by',
        'modified_by',
    ];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    private $rules = [
        'name' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
        'type' => 'required',
        'email_address' => 'required|email|regex:/(.+)@(.+)\.(.+)/i|unique:vendors,email_address,',
        'contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:11',
        'person_in_charge' => 'required|regex:/^[a-zA-Z @&$]*$/u|max:150',
        'pic_contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:11',
        'address' => 'required',
        'state' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
        'city' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
        'postcode' => 'required|regex:/^[0-9]*$/|max:5',
    ];
    /**
     * The function returns array that are required for updation.
     * @param $params
     * @return array
     */
    public function rulesForUpdation($id): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/u|max:150',
            'type' => 'required',
            'email_address' => 'required|unique:vendors,email_address,'.$id,
            'contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:11',
            'person_in_charge' => 'required|regex:/^[a-zA-Z]+$/u|max:150',
            'pic_contact_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:11',
            'address' => 'required',
            'state' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
            'city' => 'required|regex:/^[a-zA-Z0-9]*$/u|max:150',
            'postcode' => 'required|regex:/^[0-9]*$/|max:5',
        ];
    }
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
        $validator = Validator::make($input,$this->rulesForUpdation($input['id']));
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
     * @return hasMany
     */   
    public function accommodations()
    {
        return $this->hasMany('App\Models\Accommodation');
    }
    /**
     * @return hasMany
     */
    public function insurances()
    {
        return $this->hasMany('App\Models\Insurance');
    }
    /**
     * @return hasMany
     */
    public function transportations()
    {
        return $this->hasMany('App\Models\Transportation');
    }
    /**
     * @return hasMany
     */
    public function vendorAttachments()
    {
        return $this->hasMany('App\Models\VendorAttachments', 'file_id');
    }
}