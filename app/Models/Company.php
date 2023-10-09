<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company_name', 'register_number', 'country', 'state', 'pic_name', 'role', 'status', 'parent_id', 'parent_flag', 'created_by', 'modified_by'];
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'company_name' => 'required|regex:/^[a-zA-Z ]*$/',
        'register_number' => 'required|regex:/^[a-zA-Z0-9\-]*$/|unique:company,register_number,NULL,id,deleted_at,NULL',
        'country' => 'required|regex:/^[a-zA-Z ]*$/',
        'state' => 'required|regex:/^[a-zA-Z ]*$/'
    ];
    /**
     * The function returns array that are required for updation.
     * 
     * @param $id
     * @return array
     */
    public function updationRules($id): array
    {
        return [
            'id' => 'required',
            'company_name' => 'required|regex:/^[a-zA-Z ]*$/',
            'register_number' => 'required|regex:/^[a-zA-Z0-9\-]*$/|unique:company,register_number,'.$id.',id,deleted_at,NULL',
            'country' => 'required|regex:/^[a-zA-Z]*$/',
            'state' => 'required|regex:/^[a-zA-Z ]*$/'
        ];
    }
    /**
     * @return HasMany
     */
    public function userCompany()
    {
        return $this->hasMany(UserCompany::class, 'company_id');
    }
    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_company');
    }
    /**
     * @return HasMany
     */
    public function countries()
    {
        return $this->hasMany(Countries::class, 'company_id');
    }
}
