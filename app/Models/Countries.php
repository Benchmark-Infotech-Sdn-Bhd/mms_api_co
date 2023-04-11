<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Countries extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['country_name','system_type','fee','bond','costing_status','created_by','modified_by'];
    /**
     * @return HasMany
     */
    public function embassyAttestationFileCosting()
    {
        return $this->hasMany(EmbassyAttestationFileCosting::class, 'country_id');
    }
    /**
     * @return HasMany
     */
    public function agents()
    {
        return $this->hasMany(Agent::class, 'country_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'country_name' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
        'system_type' => 'required',
        'bond' => 'regex:/^[0-9]+$/|max:3'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'country_name' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
        'system_type' => 'required',
        'bond' => 'regex:/^[0-9]+$/|max:3'
    ];
}
