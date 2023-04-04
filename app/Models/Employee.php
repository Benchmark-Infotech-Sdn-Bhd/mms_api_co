<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Role;

class Employee extends Model
{
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
    'created_by','modified_by'];
    /**
     * @return BelongsTo
     */
    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    /**
     * @return BelongsTo
     */
    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'employee_name' => 'required|max:255',
        'gender' => 'required|max:15',
        'date_of_birth' => 'required|date_format:Y-m-d',
        'ic_number' => 'required|regex:/^[0-9]+$/|max:12',
        'passport_number' => 'regex:/^[a-zA-Z0-9]*$/',
        'email' => 'required|email|max:150',
        'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
        'address' => 'required',
        'postcode' => 'required|regex:/^[0-9]+$/|max:5',
        'position' => 'required|max:150',
        'branch_id' => 'required',
        'role_id' => 'required',
        'salary' => 'required',
        'status' => 'required|regex:/^[0-1]+$/|max:1'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'employee_name' => 'required|max:255',
        'gender' => 'required|max:15',
        'date_of_birth' => 'required|date_format:Y-m-d',
        'ic_number' => 'required|regex:/^[0-9]+$/|max:12',
        'passport_number' => 'regex:/^[a-zA-Z0-9]*$/',
        'email' => 'required|email|max:150',
        'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
        'address' => 'required',
        'postcode' => 'required|regex:/^[0-9]+$/|max:5',
        'position' => 'required|max:150',
        'branch_id' => 'required',
        'role_id' => 'required',
        'salary' => 'required',
        'status' => 'required|regex:/^[0-1]+$/|max:1'
    ];
}
