<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pig extends Model
{
    protected $fillable = [
        'ear_tag',
        'breed',
        'sex',
        'pen_location',
        'pig_source',
        'date_added',
        'latest_weight',
        'asset_value',
        'pen_id',
    ];

    protected $casts = [
        'date_added' => 'date',
        'latest_weight' => 'decimal:2',
        'asset_value' => 'decimal:2',
    ];

    public function pen(): BelongsTo
    {
        return $this->belongsTo(Pen::class);
    }
}