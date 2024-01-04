<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerEvent extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_event';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id', 'event_date', 'event_type', 'flight_number', 'departure_date', 'remarks', 'created_by', 'modified_by', 'last_working_date'];

    /**
     * @return BelongsTo
     */
    public function Workers()
    {
        return $this->belongsTo(Workers::class);
    }
    /**
     * @return HasMany
     */
    public function eventAttachments()
    {
        return $this->hasMany(WorkerEventAttachments::class, 'file_id');
    }
}
