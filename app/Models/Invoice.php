<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['crm_prospect_id','issue_date','due_date','reference_number','account','tax','amount','created_by','modified_by','company_id'];
   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'crm_prospect_id' => 'required|regex:/^[0-9]+$/',
        'issue_date' => 'required|date_format:Y-m-d',
        'due_date' => 'required|date_format:Y-m-d',
        'reference_number' => 'required|regex:/^[a-zA-Z0-9-]*$/',
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
            'crm_prospect_id' => 'required|regex:/^[0-9]+$/',
            'issue_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'due_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'reference_number' => 'required|regex:/^[a-zA-Z0-9-]*$/',
            'amount' => 'required|max:9|regex:/^[0-9]+(\.[0-9][0-9]?)?$/'
        ];
    }

    /**
     * @return HasMany
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItems::class, 'invoice_id');
    }

    /**
     * @return HasOne
     */
    public function crm_prospect(): HasOne
    {
        return $this->hasOne(CRMProspect::class, 'id', 'crm_prospect_id');
    }

}