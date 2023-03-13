<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sectors extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sectors';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sector_name','sub_sector_name','created_by','modified_by'];
    /**
     * @return HasMany
     */
    public function documentChecklist()
    {
        return $this->hasMany(DocumentChecklist::class, 'sector_id');
    }
    /**
     * The attributes that are required.
     *
     * @var array
     */
    public $rules = [
        'sector_name' => 'required|max:255',
        'sub_sector_name' => 'max:255'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required',
        'sector_name' => 'required|max:255',
        'sub_sector_name' => 'max:255'
    ];
}
