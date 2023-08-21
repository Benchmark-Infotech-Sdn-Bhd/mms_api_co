<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class EContractProject extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'e-contract_project';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'application_id', 'name', 'state', 'city', 'address', 'annual_leave', 'medical_leave', 'hospitalization_leave', 'created_by', 'modified_by'
    ];

    /**
     * @return HasOne
     */
    public function projectAttachments()
    {
        return $this->hasOne(EContractProjectAttachments::class, 'file_id');
    }

}
