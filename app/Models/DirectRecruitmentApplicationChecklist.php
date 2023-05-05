<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DirectrecruitmentApplications;
use OwenIt\Auditing\Contracts\Auditable;

class DirectRecruitmentApplicationChecklist extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'directrecruitment_application_checklist';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['application_id','item_name','application_checklist_status','remarks',
    'file_url','created_by','modified_by'];
    /**
     * @return BelongsTo
     */
    public function directrecruitmentApplications()
    {
        return $this->belongsTo(DirectrecruitmentApplications::class, 'application_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'application_id' => 'required|regex:/^[0-9]+$/',
        'item_name' => 'required',
        'application_checklist_status' => 'required'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required|regex:/^[0-9]+$/'
    ];
}
