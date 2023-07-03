<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerPLKSAttachments extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_plks_attachments';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['file_id', 'file_name', 'file_type', 'file_url', 'created_by', 'modified_by'];

    /**
     * @return BelongsTo
     */
    public function WorkersPLKS()
    {
        return $this->belongsTo(Workers::class);
    }
}
