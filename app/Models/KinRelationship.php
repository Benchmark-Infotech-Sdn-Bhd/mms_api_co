<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KinRelationship extends Model
{
    protected $table = 'kin_relationship';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name','status'
    ];
}