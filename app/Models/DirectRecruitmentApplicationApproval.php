<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DirectRecruitmentApplicationApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'directrecruitment_application_approval';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id', 'item_name', 'ksm_reference_number', 'received_date', 'valid_until', 'created_by', 'modified_by'];

    /**
     * @return BelongsTo
     */
    public function application()
    {
        return $this->belongsTo(DirectrecruitmentApplications::class, 'application_id');
    }

    /**
     * @return HasMany
     */
    public function approvalAttachment(): HasMany
    {
        return $this->hasMany(ApprovalAttachments::class, 'file_id');
    }
}
