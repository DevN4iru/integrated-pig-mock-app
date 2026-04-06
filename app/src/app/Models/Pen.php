<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pen extends Model
{
    protected $fillable = [
        'name',
        'type',
        'capacity',
        'notes',
    ];
}