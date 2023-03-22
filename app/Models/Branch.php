<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'branch';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['branch_name','state','city','branch_address','postcode','service_id',
    'remarks','created_by','modified_by'];
    /**
     * @return HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'service_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'branch_name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
        'state' => 'required|max:150',
        'city' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
        'branch_address' => 'required',
        'postcode' => 'required|regex:/^[0-9]+$/|max:5',
        'service_id' => 'required'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'branch_name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
        'state' => 'required|max:150',
        'city' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
        'branch_address' => 'required',
        'postcode' => 'required|regex:/^[0-9]+$/|max:5',
        'service_id' => 'required'
    ];
}
