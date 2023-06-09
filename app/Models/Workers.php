<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Workers extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'workers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['onboarding_country_id','agent_id','application_id','name','gender','date_of_birth','passport_number',
    'passport_valid_until','fomema_valid_until','address','status',
    'city','state','created_by','modified_by'];
   
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'onboarding_country_id' => 'required|regex:/^[0-9]+$/',
        'agent_id' => 'required|regex:/^[0-9]+$/',
        'application_id' => 'required|regex:/^[0-9]+$/',
        'name' => 'required|regex:/^[a-zA-Z]*$/|max:255',
        'date_of_birth' => 'required|date_format:Y-m-d',
        'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
        'passport_number' => 'required|passport_number|regex:/^[a-zA-Z0-9]*$/|unique:workers,passport_number,NULL,id,deleted_at,NULL',
        'passport_valid_until' => 'required|date_format:Y-m-d',
        'fomema_valid_until' => 'required|date_format:Y-m-d',
        'address' => 'required',
        'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
        'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150'
        
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
            'onboarding_country_id' => 'required|regex:/^[0-9]+$/',
            'agent_id' => 'required|regex:/^[0-9]+$/',
            'application_id' => 'required|regex:/^[0-9]+$/',
            'name' => 'required|regex:/^[a-zA-Z]*$/|max:255',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
            'passport_number' => 'required|passport_number|regex:/^[a-zA-Z0-9]*$/|unique:workers,passport_number,'.$id.',id,deleted_at,NULL',
            'passport_valid_until' => 'required|date_format:Y-m-d',
            'fomema_valid_until' => 'required|date_format:Y-m-d',
            'address' => 'required',
            'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150'            
        ];
    }
}
