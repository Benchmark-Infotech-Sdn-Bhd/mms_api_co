<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentChecklist extends Model
{
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
    protected $fillable = ['sector_id','document_title','created_by','modified_by'];
    /**
     * @return BelongsTo
     */
    public function sectors()
    {
        return $this->belongsTo(Sectors::class, 'sector_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'sector_id' => 'required',
        'document_title' => 'required'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'sector_id' => 'required',
        'document_title' => 'required'
    ];
}
