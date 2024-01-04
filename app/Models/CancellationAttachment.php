<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancellationAttachment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'cancellation_attachment';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'file_id', 'file_name', 'file_type', 'file_url', 'created_by', 'modified_by', 'remarks'
    ];
    /**
     * @return BelongsTo
     */
    public function workerCancellation()
    {
        return $this->belongsTo(Workers::class);
    }
}
