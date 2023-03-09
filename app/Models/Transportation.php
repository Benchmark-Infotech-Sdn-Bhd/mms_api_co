<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
class Transportation extends Model
{
    use SoftDeletes;
    protected $table = 'transportation';

    protected $fillable = [
        'driver_name',
        'driver_contact_number',
        'driver_license_number',
        'vehicle_type',
        'number_plate',
        'vehicle_capacity',
        'vendor_id',
    ];

    public static $rules = [
        'driver_name' => 'required',
        'driver_contact_number' => 'required',
        'driver_license_number' => 'required',
        'vehicle_type' => 'required',
        'number_plate' => 'required',
        'vehicle_capacity' => 'required',
    ];

    public static function validate($input) {
        $validator = Validator::make($input, static::$rules);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return true;
    }
    /**
     * @return belongsTo
     */
    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor');
    }
}
