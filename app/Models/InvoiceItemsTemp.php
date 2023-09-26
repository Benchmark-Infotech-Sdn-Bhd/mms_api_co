<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvoiceItemsTemp extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_items_temp';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['crm_prospect_id','service_id','expense_id','invoice_number','item','description','quantity','price','account','tax_rate','total_price','created_by','modified_by'];

    /**
     * @return HasOne
     */
    public function crm_prospect(): HasOne
    {
        return $this->hasOne(CRMProspect::class, 'id', 'crm_prospect_id');
    }

}