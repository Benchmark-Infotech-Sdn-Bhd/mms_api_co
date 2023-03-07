<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accommodation extends Model
{
    use SoftDeletes;
    protected $table = 'accommodation';

    protected $fillable = [
        'accommodation_name',
        'number_of_units',
        'number_of_rooms',
        'maximum_pax_per_room',
        'cost_per_pax',
        'attachment',
        'rent_deposit',
        'rent_per_month',
        'rent_advance',
        'vendor_id',
    ];

    public static $rules = [
        'accommodation_name' => 'required',
        'number_of_units' => 'required',
        'number_of_rooms' => 'required',
        'maximum_pax_per_room' => 'required',
        'cost_per_pax' => 'required',
        'attachment' => 'required',
        'rent_deposit' => 'required',
        'rent_per_month' => 'required',
    ];

    public static function validate($input) {
        $validator = Validator::make($input, static::$rules);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return true;
    }
    
    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor');
    }
}
