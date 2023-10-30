<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Role;
use OwenIt\Auditing\Contracts\Auditable;

class Employee extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employee';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['employee_name','gender','date_of_birth','ic_number','passport_number',
    'email','contact_number','address','postcode','position','branch_id','role_id','salary','status',
    'city','state','created_by','modified_by', 'company_id'];
    /**
     * @return BelongsTo
     */
    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'employee_name' => 'required|max:255',
        'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
        'date_of_birth' => 'required|date_format:Y-m-d',
        'ic_number' => 'required|regex:/^[0-9]+$/|max:12',
        'passport_number' => 'regex:/^[a-zA-Z0-9]*$/',
        'email' => 'required|email|max:150|unique:users,email,NULL,id,deleted_at,NULL',
        'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
        'address' => 'required',
        'postcode' => 'required|regex:/^[0-9]+$/|max:5',
        'position' => 'required|max:150',
        'branch_id' => 'required|regex:/^[0-9]+$/',
        'role_id' => 'required|regex:/^[0-9]+$/',
        'salary' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
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
            'employee_name' => 'required|max:255',
            'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'ic_number' => 'required|regex:/^[0-9]+$/|max:12',
            'passport_number' => 'regex:/^[a-zA-Z0-9]*$/',
            'email' => 'required|email|max:150|unique:users,email,'.$id.',reference_id,deleted_at,NULL',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'address' => 'required',
            'postcode' => 'required|regex:/^[0-9]+$/|max:5',
            'position' => 'required|max:150',
            'branch_id' => 'required|regex:/^[0-9]+$/',
            'role_id' => 'required|regex:/^[0-9]+$/',
            'salary' => 'required|regex:/^(([0-9]*)(\.([0-9]{0,2}+))?)$/',
            'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150'
        ];
    }
    /**
     * The function returns array that are required for updation.
     * @param $params
     * @return array
     */
    public function rulesForProfileUpdation($id): array
    {
        // Unique name with deleted at
        return [
            'id' => 'required|regex:/^[0-9]+$/',
            'employee_name' => 'required|max:255',
            'email' => 'required|email|max:150|unique:users,email,'.$id.',reference_id,deleted_at,NULL',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'address' => 'required',
            'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150'
        ];
    }
    /**
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'reference_id')->where('user_type', 'Employee');
    }
}
