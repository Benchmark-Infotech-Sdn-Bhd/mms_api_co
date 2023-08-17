<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EContractApplicationAttachments extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'e-contract_application_attachments';

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
    public function eContractApplications()
    {
        return $this->belongsTo(EContractApplications::class);
    }
}
