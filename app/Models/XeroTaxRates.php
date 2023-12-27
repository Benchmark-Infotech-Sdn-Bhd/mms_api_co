<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class XeroTaxRates extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'xero_tax_rates';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'name', 'tax_type', 'report_tax_type', 'can_applyto_assets', 'can_applyto_equity', 'can_applyto_expenses', 'can_applyto_liabilities', 'can_applyto_revenue', 'display_tax_rate', 'effective_rate', 'status', 'company_id', 'tax_id', 'tax_specific_type', 'output_tax_account_name', 'purchase_tax_account_name', 'tax_account_id', 'purchase_tax_account_id', 'is_inactive', 'is_value_added', 'is_default_tax', 'is_editable', 'last_modified_time', 'created_by', 'modified_by'
    ];
}
