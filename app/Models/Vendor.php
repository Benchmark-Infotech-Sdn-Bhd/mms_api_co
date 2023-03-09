<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    protected $table = 'vendors';

    protected $fillable = [
        'name',
        'state',
        'type',
        'person_in_charge',
        'contact_number',
        'email_address',
        'address',

    ];

    public static $rules = [
        'name' => 'required',
        'state' => 'required',
        'type' => 'required',
        'person_in_charge' => 'required',
        'contact_number' => 'required',
        'email_address' => 'required',
        'address' => 'required',
    ];

    public static function validate($input) {
        $validator = Validator::make($input, static::$rules);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return true;
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
}