<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EContractPayrollAttachments extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'e-contract_payroll_attachments';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'month', 'year', 'file_id', 'file_name', 'file_type', 'file_url', 'created_by', 'modified_by'
    ];
}
