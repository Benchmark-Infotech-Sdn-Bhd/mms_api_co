<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TotalManagementCostManagement extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'total_management_cost_management';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id','title','payment_reference_number','quantity','amount','payment_date','remarks','created_by','modified_by','invoice_number'];
   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'application_id' => 'required|regex:/^[0-9]+$/',
        'title' => 'required|max:255',
        'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9-]*$/',
        'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
        'amount' => 'required|max:9|regex:/^[0-9]+(\.[0-9][0-9]?)?$/'
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
            'application_id' => 'required|regex:/^[0-9]+$/',
            'title' => 'required|max:255',
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9-]*$/',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'amount' => 'required|max:9|regex:/^[0-9]+(\.[0-9][0-9]?)?$/'
        ];
    }

    /**
     * @return HasMany
     */
    public function totalManagementCostManagementAttachments()
    {
        return $this->hasMany(TotalManagementCostManagementAttachments::class, 'file_id');
    }

}