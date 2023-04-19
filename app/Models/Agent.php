<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Countries;
use OwenIt\Auditing\Contracts\Auditable;

class Agent extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agent';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['agent_name','country_id','city','person_in_charge','pic_contact_number',
    'email_address','company_address','created_by','modified_by'];
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
        'agent_name' => 'required|regex:/^[a-zA-Z ]*$/|max:250',
        'country_id' => 'required|regex:/^[0-9]+$/',
        'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
        'person_in_charge' => 'required|max:255',
        'pic_contact_number' => 'required|regex:/^[0-9]+$/|max:11',
        'email_address' => 'required|email|unique:agent,email_address,NULL,id,deleted_at,NULL'
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
            'id' => 'required|regex:/^[0-9]+$/',
            'agent_name' => 'required|regex:/^[a-zA-Z ]*$/|max:250',
            'country_id' => 'required|regex:/^[0-9]+$/',
            'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
            'person_in_charge' => 'required|max:255',
            'pic_contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'email_address' => 'required|email|unique:agent,email_address,'.$id.',id,deleted_at,NULL'
        ];
    }
}
