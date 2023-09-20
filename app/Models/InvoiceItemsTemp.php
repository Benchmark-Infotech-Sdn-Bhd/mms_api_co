<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InvoiceItemsTemp extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
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
    protected $fillable = ['service_id','expense_id','invoice_number','item','description','quantity','price','account','tax_rate','total_price','created_by','modified_by'];

}