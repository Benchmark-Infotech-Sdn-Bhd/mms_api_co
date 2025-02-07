<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkerKin extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'worker_kin';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['worker_id','kin_name','kin_relationship_id','kin_contact_number','created_by','modified_by'];
   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'worker_id' => 'required|regex:/^[0-9]+$/',
        'kin_name' => 'required|regex:/^[a-zA-Z]*$/|max:255',
        'kin_relationship_id' => 'required|regex:/^[0-9]+$/',
        'kin_contact_number' => 'required|regex:/^[0-9]+$/'
    ];
    /**
     * The function returns array that are required for updation.
     * @param $params
     * @return array
     */
    public function rulesForUpdation($id): array
    {
        // Unique name with deleted at
        return [
            'worker_id' => 'required|regex:/^[0-9]+$/',
            'kin_name' => 'required|regex:/^[a-zA-Z]*$/|max:255',
            'kin_relationship_id' => 'required|regex:/^[0-9]+$/',
            'kin_contact_number' => 'required|regex:/^[0-9]+$/'
        ];
    }

    /**
     * @return BelongsTo
     */
    public function Workers()
    {
        return $this->belongsTo(Workers::class);
    }
}
