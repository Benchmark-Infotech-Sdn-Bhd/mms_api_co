<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
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
        'agent_name' => 'required|max:250',
        'country_id' => 'required',
        'city' => 'max:150',
        'person_in_charge' => 'required|max:255',
        'pic_contact_number' => 'required|max:20',
        'email_address' => 'required|email',
        'company_address' => 'required'
    ];
}
