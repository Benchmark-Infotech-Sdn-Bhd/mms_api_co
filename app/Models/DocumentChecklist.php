<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Sectors;
use App\Models\DocumentChecklistAttachments;
use OwenIt\Auditing\Contracts\Auditable;

class DocumentChecklist extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_checklist';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sector_id','document_title','remarks','created_by','modified_by'];
    /**
     * @return BelongsTo
     */
    public function sectors()
    {
        return $this->belongsTo(Sectors::class, 'sector_id');
    }
    /**
     * @return HasMany
     */
    public function documentChecklistAttachments()
    {
        return $this->hasOne(DocumentChecklistAttachments::class, 'document_checklist_id');
    }

    /**
     * @return HasMany
     */
    public function applicationChecklistAttachments()
    {
        return $this->hasOne(ApplicationChecklistAttachments::class, 'document_checklist_id');
    }

    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'sector_id' => 'required|regex:/^[0-9]+$/',
        'document_title' => 'required'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required|regex:/^[0-9]+$/',
        'sector_id' => 'required|regex:/^[0-9]+$/',
        'document_title' => 'required'
    ];
}
