<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmSetting extends Model
{
    protected $fillable = [
        'price_per_kg',
        'alert_recipient_email',
        'feed_reminder_time',
    ];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
    ];

    public static function current(): self
    {
        $setting = static::query()->find(1);

        if (!$setting) {
            $setting = static::query()->create([
                'id' => 1,
                'price_per_kg' => 0,
            ]);
        }

        return $setting;
    }

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
