<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerPostArrival extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_post_arrival';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id', 'arrival_id', 'status','arrived_date', 'entry_visa_valid_until', 'jtk_submitted_on', 'new_arrival_date', 'flight_number', 'arrival_time', 'remarks','created_by', 'modified_by'];

    protected $appends = ['worker_post_arrival_attachments'];

    /**
     * @return BelongsTo
     */
    public function workers()
    {
        return $this->belongsTo(Workers::class);
    }
    /**
     * @return HasMany
     */
    public function workerPostArrivalAttachments()
    {
        return $this->hasMany(WorkerPostArrivalAttachments::class, 'file_id');
    }

    public $worker_post_arrival_attachments_temp = null;

    public function setWorkerPostArrivalAttachmentsTempAttribute(array $value)
    {
        return $this->worker_post_arrival_attachments_temp = $value;
    }
    public function getWorkerPostArrivalAttachmentsTempAttribute()
    {
        return $this->worker_post_arrival_attachments_temp;
    } 
    public function getWorkerPostArrivalAttachmentsAttribute()
    {
        return $this->workerPostArrivalAttachments()->get();
    }
}
