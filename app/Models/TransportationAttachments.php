<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportationAttachments extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transportation_attachments';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['file_id', 'file_name', 'file_type', 'file_url'];

    /**
     * @return BelongsTo
     */
    public function transportation()
    {
        return $this->belongsTo('App\Models\Transportation');
    }
}
