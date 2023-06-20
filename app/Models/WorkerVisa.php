<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerVisa extends Model implements Auditable
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
    protected $fillable = ['worker_id','ksm_reference_number','calling_visa_reference_number','calling_visa_valid_until','entry_visa_valid_until','work_permit_valid_until','created_by','modified_by'];

    protected $appends = ['worker_visa_attachments'];
   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'worker_id' => 'required|regex:/^[0-9]+$/',
        'ksm_reference_number' => 'required|regex:/^[a-zA-Z]*$/|max:255'
    ];
    /**
     * The function returns array that are required for updation.
     * @param $params
     * @return array
     */
    public function rulesForUpdation($id): array
    {
        // Unique name with deleted at
        return [
            'worker_id' => 'required|regex:/^[0-9]+$/',
            'ksm_reference_number' => 'required|regex:/^[a-zA-Z]*$/|max:255'
        ];
    }

    /**
     * @return BelongsTo
     */
    public function Workers()
    {
        return $this->belongsTo(Workers::class);
    }


    public $worker_visa_attachments_temp = null;

    public function setWorkerVisaAttachmentsTempAttribute(array $value)
    {
        return $this->worker_visa_attachments_temp = $value;
    }
    public function getWorkerVisaAttachmentsTempAttribute()
    {
        return $this->worker_visa_attachments_temp;
    } 
    public function getWorkerVisaAttachmentsAttribute()
    {
        return $this->WorkerVisaAttachments()->get();
    }

    /**
     * @return HasMany
     */
    public function workerVisaAttachments()
    {
        return $this->hasMany(WorkerVisaAttachments::class, 'file_id');
    }
}
