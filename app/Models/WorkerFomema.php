<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerFomema extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_fomema';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id','purchase_date','clinic_name','doctor_code','allocated_xray','xray_code', 'fomema_total_charge', 'convenient_fee', 'fomema_status', 'created_by','modified_by'];
   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        
    ];
    /**
     * The function returns array that are required for updation.
     * @param $params
     * @return array
     */
    public function rulesForUpdation($id): array
    {
        // Unique name with deleted at
        return [
           
        ];
    }

    /**
     * @return BelongsTo
     */
    public function Workers()
    {
        return $this->belongsTo(Workers::class);
    }
}
