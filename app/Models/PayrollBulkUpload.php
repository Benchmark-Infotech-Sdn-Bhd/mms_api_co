<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollBulkUpload extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payroll_bulk_upload';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id','name','type', 'total_records', 'total_success', 'total_failure', 'failure_case_url', 'actual_row_count', 'process_status', 'created_by', 'modified_by', 'company_id'];
   
}
