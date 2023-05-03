<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Countries;
use OwenIt\Auditing\Contracts\Auditable;

class EmbassyAttestationFileCosting extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
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
    protected $fillable = ['country_id','title','amount','created_by','modified_by'];
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
        'country_id' => 'required|regex:/^[0-9]+$/',
        'title' => 'required',
        'amount' => 'required|regex:/^(([0-9]*)(\.([0-9]{0,2}+))?)$/'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required|regex:/^[0-9]+$/',
        'country_id' => 'required|regex:/^[0-9]+$/',
        'title' => 'required',
        'amount' => 'required|regex:/^(([0-9]*)(\.([0-9]{0,2}+))?)$/'
    ];
}
