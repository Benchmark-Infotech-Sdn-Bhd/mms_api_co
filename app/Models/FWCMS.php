<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FWCMS extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fwcms';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id', 'submission_date', 'applied_quota', 'status', 'ksm_reference_number', 'remarks', 'created_by', 'modified_by'];

    /**
     * @return BelongsTo
     */
    public function application()
    {
        return $this->belongsTo(DirectrecruitmentApplications::class, 'application_id');
    }
}
