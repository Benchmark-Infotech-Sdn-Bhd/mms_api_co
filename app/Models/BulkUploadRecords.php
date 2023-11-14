<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BulkUploadRecords extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'bulk_upload_records';
    protected $fillable = ['bulk_upload_id', 'parameter', 'comments', 'status', 'success_flag'];
}
