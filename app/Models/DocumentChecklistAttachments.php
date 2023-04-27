<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DocumentChecklist;
use OwenIt\Auditing\Contracts\Auditable;

class DocumentChecklistAttachments extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_checklist_attachments';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['document_checklist_id','application_id','file_type','file_url','created_by','modified_by'];
    /**
     * @return BelongsTo
     */
    public function documentChecklist()
    {
        return $this->belongsTo(DocumentChecklist::class, 'document_checklist_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'document_checklist_id' => 'required|regex:/^[0-9]+$/',
        'application_id' => 'required|regex:/^[0-9]+$/'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required|regex:/^[0-9]+$/',
        'document_checklist_id' => 'required|regex:/^[0-9]+$/',
        'application_id' => 'required|regex:/^[0-9]+$/'
    ];
}
