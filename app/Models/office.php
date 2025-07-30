<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class office extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius',
    ];
}
