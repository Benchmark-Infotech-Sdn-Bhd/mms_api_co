<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeRegistration extends Model
{
    use SoftDeletes;
    protected $table = 'fee_registration';

    protected $fillable = [
        'item',
        'fee_per_pax',
        'type',
        'applicable_for',
        'sectors',
    ];

    public static $rules = [
        'item' => 'required',
        'fee_per_pax' => 'required',
        'type' => 'required',
        'applicable_for' => 'required',
        'sectors' => 'required',
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
    public function feeRegistration()
    {
        return $this->hasMany(FeeRegistration::class);
    }
}
