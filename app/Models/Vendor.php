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
        'person_in_charge'
    ];

    public static $rules = [
        'name' => 'required',
        'state' => 'required',
        'type' => 'required',
        'person_in_charge' => 'required',
    ];

    public static function validate($input) {
        $validator = Validator::make($input, static::$rules);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return true;
    }
    
    public function accommodations()
    {
        return $this->hasMany('App\Models\Accommodation');
    }
    public function insurances()
    {
        return $this->hasMany('App\Models\Insurance');
    }
    public function transportations()
    {
        return $this->hasMany('App\Models\Transportation');
    }
}