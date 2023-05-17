<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationInterviews extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'application_interviews';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id', 'item_name', 'ksm_reference_number', 'schedule_date', 'approved_quota', 'approval_date', 'status', 'remarks', 'created_by', 'modified_by'];

    /**
     * @return HasMany
     */
    public function applicationInterviewAttachments()
    {
        return $this->hasMany(ApplicationInterviewAttachments::class, 'file_id');
    }

    /**
     * @return BelongsTo
     */
    public function application()
    {
        return $this->belongsTo(DirectrecruitmentApplications::class, 'application_id');
    }
}
