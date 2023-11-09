<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class XeroItems extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'xero_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'item_id', 'code', 'description', 'purchase_description', 'name', 'is_tracked_as_inventory', 'is_sold', 'is_purchased', 'created_by', 'modified_by'
    ];
}
