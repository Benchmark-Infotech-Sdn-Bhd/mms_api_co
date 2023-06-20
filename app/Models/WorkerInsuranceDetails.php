<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerInsuranceDetails extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_insurance_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id','ig_policy_number','ig_policy_number_valid_until','hospitalization_policy_number','hospitalization_policy_number_valid_until','created_by','modified_by', 'insurance_provider_id', 'ig_amount', 'hospitalization_amount', 'insurance_submitted_on', 'insurance_expiry_date', 'insurance_status'];
   
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
