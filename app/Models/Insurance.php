<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insurance extends Model
{
    use SoftDeletes;
    protected $table = 'insurance';

    protected $fillable = [
        'create_insurance',
        'no_of_worker_from',
        'no_of_worker_to',
        'fee_per_pax',
        'vendor_id',
    ];

    public static $rules = [
        'create_insurance' => 'required',
        'no_of_worker_from' => 'required',
        'no_of_worker_to' => 'required',
        'fee_per_pax' => 'required',
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
