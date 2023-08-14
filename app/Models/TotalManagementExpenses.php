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
    protected $fillable = ['worker_id','title','payment_reference_number','quantity','amount','payment_date','remarks','created_by','modified_by','type','deduction'];
   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'worker_id' => 'required|regex:/^[0-9]+$/',
        'title' => 'required|max:255',
        'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
        'amount' => 'required|regex:/^(\d+(,\d{1,2})?)?$/',
        'type' => 'required'
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
            'id' => 'required|regex:/^[0-9]+$/',
            'worker_id' => 'required|regex:/^[0-9]+$/',
            'title' => 'required|max:255',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'amount' => 'required|regex:/^(\d+(,\d{1,2})?)?$/',
            'type' => 'required'
        ];
    }

    /**
     * @return HasMany
     */
    public function totalManagementExpensesAttachments()
    {
        return $this->hasMany(TotalManagementExpensesAttachments::class, 'file_id');
    }

}