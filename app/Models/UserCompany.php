<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCompany extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_company';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'company_id', 'role_id', 'created_by', 'modified_by'];
    /**
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
