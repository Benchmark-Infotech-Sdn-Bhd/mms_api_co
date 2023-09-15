<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EContractCostManagement extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'e-contract_cost_management';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id','title','payment_reference_number','quantity','amount','payment_date','remarks','created_by','modified_by', 'invoice_id', 'invoice_status'];
   
    /**
     * @return HasMany
     */
    public function eContractCostManagementAttachments()
    {
        return $this->hasMany(EContractCostManagementAttachments::class, 'file_id');
    }

}