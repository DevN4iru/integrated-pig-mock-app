<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pig extends Model
{
    protected $fillable = [
        'ear_tag',
        'breed',
        'sex',
        'pen_id',
        'pen_location',
        'pig_source',
        'date_added',
        'latest_weight',
        'asset_value',
    ];

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    public function healthLogs()
    {
        return $this->hasMany(HealthLog::class)->latest();
    }

    // 🔥 NEW
    public function medications()
    {
        return $this->hasMany(Medication::class)->latest();
    }
}