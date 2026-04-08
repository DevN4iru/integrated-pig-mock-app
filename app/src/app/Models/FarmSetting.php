<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmSetting extends Model
{
    protected $fillable = [
        'price_per_kg',
    ];

    public static function currentPricePerKg(): float
    {
        $setting = static::query()->find(1);

        if (!$setting) {
            $setting = static::query()->create([
                'id' => 1,
                'price_per_kg' => 0,
            ]);
        }

        return (float) $setting->price_per_kg;
    }
}