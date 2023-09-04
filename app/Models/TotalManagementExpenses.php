<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TotalManagementExpenses extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'total_management_expenses';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id', 'application_id', 'project_id', 'title','type', 'payment_reference_number', 'payment_date', 'amount', 'amount_paid', 'deduction', 'remaining_amount', 'remarks', 'created_by', 'modified_by'];
   
    /**
     * @return HasMany
     */
    public function totalManagementExpensesAttachments()
    {
        return $this->hasMany(TotalManagementExpensesAttachments::class, 'file_id');
    }

}