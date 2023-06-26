<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerBioMedical extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_bio_medical';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id','bio_medical_reference_number','bio_medical_valid_until','created_by','modified_by'];

    protected $appends = ['worker_bio_medical_attachments'];
   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'worker_id' => 'required|regex:/^[0-9]+$/',
        'bio_medical_reference_number' => 'required|regex:/^[a-zA-Z]*$/|max:255',
        'bio_medical_valid_until' => 'required|date_format:Y-m-d'
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
            'bio_medical_reference_number' => 'required|regex:/^[a-zA-Z]*$/|max:255',
            'bio_medical_valid_until' => 'required|date_format:Y-m-d'
        ];
    }

    /**
     * @return BelongsTo
     */
    public function Workers()
    {
        return $this->belongsTo(Workers::class);
    }

    public $worker_bio_medical_attachments_temp = null;

    public function setWorkerBioMedicalAttachmentsTempAttribute(array $value)
    {
        return $this->worker_bio_medical_attachments_temp = $value;
    }
    public function getWorkerBioMedicalAttachmentsTempAttribute()
    {
        return $this->worker_bio_medical_attachments_temp;
    } 
    public function getWorkerBioMedicalAttachmentsAttribute()
    {
        return $this->WorkerBioMedicalAttachments()->get();
    }

    /**
     * @return HasMany
     */
    public function workerBioMedicalAttachments()
    {
        return $this->hasMany(WorkerBioMedicalAttachments::class, 'file_id');
    }
}
