<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentChecklist extends Model
{
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
}
