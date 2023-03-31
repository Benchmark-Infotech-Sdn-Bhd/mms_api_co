<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchesServices extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'branch_services';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['branch_id', 'service_id', 'service_name', 'status'];

    /**
     * @return BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }
}
