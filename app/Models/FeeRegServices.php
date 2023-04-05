<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeRegServices extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fee_registration_services';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['fee_reg_id', 'service_id', 'service_name', 'status'];

    /**
     * @return BelongsTo
     */
    public function FeeRegistration()
    {
        return $this->belongsTo('App\Models\FeeRegistration');
    }
}
