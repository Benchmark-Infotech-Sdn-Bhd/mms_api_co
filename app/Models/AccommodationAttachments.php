<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationAttachments extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accommodation_attachments';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['file_id', 'file_name', 'file_type', 'file_url'];

    /**
     * @return belongsTo
     */
    public function accommodationData()
    {
        return $this->belongsTo('App\Models\Accommodation');
    }
}
