<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Countries extends Model
{
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
    protected $fillable = ['country_name','system_type','fee','costing_status','created_by','modified_by'];
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
        'system_type' => 'required'
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
        'costing_status' => 'required'
    ];
}
