<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class XeroAccounts extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'xero_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $fillable = [
        'account_id', 'code', 'name', 'status', 'type', 'tax_type','class','enable_payments_to_account','show_in_expense_claims', 'bank_account_number','bank_account_type', 'currency_code', 'reporting_code', 'reporting_code_name', 'created_by', 'modified_by'
    ];
}
