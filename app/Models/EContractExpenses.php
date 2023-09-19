<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EContractExpenses extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'e-contract_expenses';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id', 'application_id', 'project_id', 'title', 'type', 'payment_reference_number', 'payment_date', 'amount', 'amount_paid', 'deduction', 'remaining_amount', 'remarks', 'created_by', 'modified_by','is_payroll','payroll_id','month','year'];
    
    /**
     * @return HasMany
     */
    public function eContractExpensesAttachments()
    {
        return $this->hasMany(EContractExpensesAttachments::class, 'file_id');
    }

}