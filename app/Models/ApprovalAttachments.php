<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAttachments extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'approval_attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'file_id', 'file_name', 'file_type', 'file_url', 'created_by', 'modified_by'
    ];
    /**
     * @return BelongsTo
     */
    public function directRecruitmentApplicationApproval()
    {
        return $this->belongsTo(DirectRecruitmentApplicationApproval::class, 'file_id');
    }
}