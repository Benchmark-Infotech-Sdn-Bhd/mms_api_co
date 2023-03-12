<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmbassyAttestationFileCosting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'embassy_attestation_file_costing';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['country_id','title','fee','created_by','modified_by'];
    /**
     * @return BelongsTo
     */
    public function countries()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'country_id' => 'required',
        'title' => 'required',
        'amount' => 'required'
    ];
}
