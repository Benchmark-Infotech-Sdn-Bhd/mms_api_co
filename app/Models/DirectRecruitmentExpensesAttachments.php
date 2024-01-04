<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectRecruitmentExpensesAttachments extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'directrecruitment_expenses_attachments';

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
    public function directRecruitmentExpenses()
    {
        return $this->belongsTo(directRecruitmentExpenses::class);
    }
}