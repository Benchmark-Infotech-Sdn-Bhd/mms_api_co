<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Levy extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'levy';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id', 'item', 'payment_date', 'payment_amount', 'approved_quota', 'status', 'ksm_reference_number', 'payment_reference_number', 'approval_number', 'new_ksm_reference_number', 'remarks', 'created_by', 'modified_by'];

    /**
     * @return BelongsTo
     */
    public function application()
    {
        return $this->belongsTo(DirectrecruitmentApplications::class, 'application_id');
    }
    /**
     * Function to get Levy Paid KSM reference Number
     * 
     * @param $application_id
     * @return array
     */
    public function levyKSM($application_id): array
    {
        $ksmReferenceNumbers = Levy::where('application_id', $application_id)
                ->where('status', 'Paid')
                ->select('ksm_reference_number')
                ->get()
                ->toArray();
        return (count($ksmReferenceNumbers)) ? array_column($ksmReferenceNumbers, 'ksm_reference_number') : [];
    }
}
