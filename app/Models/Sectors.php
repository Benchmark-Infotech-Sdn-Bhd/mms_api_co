<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class Sectors extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
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
    protected $fillable = ['sector_name','sub_sector_name','checklist_status','status','created_by','modified_by', 'company_id'];
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
        'sector_name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
        'sub_sector_name' => 'regex:/^[a-zA-Z ]*$/|max:255'
    ];
    /**
     * The attributes that are required for updation.
     *
     * @var array
     */
    public $rulesForUpdation = [
        'id' => 'required|regex:/^[0-9]+$/',
        'sector_name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
        'sub_sector_name' => 'regex:/^[a-zA-Z ]*$/|max:255'
    ];
    /**
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
