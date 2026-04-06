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

    public function medications()
    {
        return $this->hasMany(Medication::class)->latest();
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class)->latest();
    }

    public function mortalityLogs()
    {
        return $this->hasMany(MortalityLog::class)->latest();
    }

    public function sales()
    {
        return $this->hasMany(Sale::class)->latest();
    }
}