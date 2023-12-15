<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessControlURL extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accommodation';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['module_id', 'module_name', 'url', 'status'];
}
