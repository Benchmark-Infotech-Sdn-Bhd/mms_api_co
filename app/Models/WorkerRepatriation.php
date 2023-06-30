<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerRepatriation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_visa';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id', 'flight_number', 'flight_date', 'expenses', 'checkout_memo_reference_number', 'created_by', 'modified_by'];
    /**
     * @return BelongsTo
     */
    public function WorkersRepatriation()
    {
        return $this->belongsTo(Workers::class);
    }
}
