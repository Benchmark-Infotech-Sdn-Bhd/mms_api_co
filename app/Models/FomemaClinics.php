<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class FomemaClinics extends Model
{
    use SoftDeletes;
    protected $table = 'fomema_clinics';

    protected $fillable = [
        'clinic_name',
        'person_in_charge',
        'pic_contact_number',
        'address',
        'state',
        'city',
        'postcode',
    ];

    public static $rules = [
        'clinic_name' => 'required',
        'person_in_charge' => 'required',
        'pic_contact_number' => 'required',
        'address' => 'required',
        'state' => 'required',
        'city' => 'required',
        'postcode' => 'required',
    ];

    public static function validate($input) {
        $validator = Validator::make($input, static::$rules);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return true;
    }

    public function fomemaClinics()
    {
        return $this->hasMany(FomemaClinics::class);
    }
}
