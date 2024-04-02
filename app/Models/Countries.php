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
    protected $fillable = ['country_name','system_type','fee','bond','costing_status','status','created_by','modified_by', 'company_id'];
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
     * @return HasMany
     */
    public function onboardingCountries()
    {
        return $this->hasMany(DirectRecruitmentOnboardingCountry::class, 'country_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'country_name' => 'required|regex:/^[a-zA-Z ]*$/',
        'system_type' => 'required|regex:/^[a-zA-Z]*$/',
        'bond' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
        'fee' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/'
    ];
    /**
     * The function returns array that are required for updation.
     * @param int $id
     * @param int $company_id
     * @return array
     */
    public function rulesForUpdation($id, $company_id): array
    {
        // Unique name with deleted at
        return [
            'id' => 'required|regex:/^[0-9]+$/',
            'country_name' => 'required|regex:/^[a-zA-Z ]*$/|max:150|unique:countries,country_name,'.$id.',id,deleted_at,NULL,company_id,'.$company_id,
            'system_type' => 'required|regex:/^[a-zA-Z]*$/',
            'bond' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'fee' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/'
        ];
    }
}
