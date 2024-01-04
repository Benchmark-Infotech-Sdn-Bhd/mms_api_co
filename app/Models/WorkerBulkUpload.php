<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerBulkUpload extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_bulk_upload';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['onboarding_country_id','agent_id','application_id','name','type', 'total_records', 'total_success', 'total_failure', 'failure_case_url', 'actual_row_count', 'process_status', 'user_type', 'created_by', 'modified_by', 'module_type', 'company_id'];
   
    /**
     * @return HasMany
     */
    public function records()
    {
        return $this->hasMany(BulkUploadRecords::class, 'bulk_upload_id');
    }
}
