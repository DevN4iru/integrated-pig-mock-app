<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pig extends Model
{
    use SoftDeletes;

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

    protected $appends = [
        'computed_weight',
        'computed_asset_value',
        'weight_gain',
        'daily_gain',
        'growth_status',
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

    public function feedLogs()
    {
        return $this->hasMany(FeedLog::class)->latest();
    }

    protected function orderedWeightLogs()
    {
        return $this->healthLogs()
            ->where('purpose', 'weight_update')
            ->whereNotNull('weight')
            ->orderByDesc('log_date')
            ->orderByDesc('id');
    }

    public function getComputedWeightAttribute()
    {
        $latestLog = $this->orderedWeightLogs()->first();

        return $latestLog?->weight ?? $this->latest_weight;
    }

    public function getComputedAssetValueAttribute()
    {
        $weight = $this->computed_weight;

        if ($weight === null || $weight === '') {
            return 0;
        }

        return (float) $weight * FarmSetting::currentPricePerKg();
    }

    public function getWeightGainAttribute()
    {
        $logs = $this->orderedWeightLogs()
            ->take(2)
            ->get()
            ->values();

        if ($logs->count() >= 2) {
            return (float) $logs[0]->weight - (float) $logs[1]->weight;
        }

        if ($logs->count() === 1 && $this->latest_weight !== null && $this->latest_weight !== '') {
            return (float) $logs[0]->weight - (float) $this->latest_weight;
        }

        return null;
    }

    public function getDailyGainAttribute()
    {
        $logs = $this->orderedWeightLogs()
            ->take(2)
            ->get()
            ->values();

        if ($logs->count() >= 2) {
            $latest = $logs[0];
            $previous = $logs[1];

            $days = max(
                1,
                Carbon::parse($latest->log_date)->diffInDays(Carbon::parse($previous->log_date))
            );

            $gain = (float) $latest->weight - (float) $previous->weight;

            return $gain / $days;
        }

        if ($logs->count() === 1 && $this->latest_weight !== null && $this->latest_weight !== '') {
            $latest = $logs[0];
            $baselineDate = $this->date_added ? Carbon::parse($this->date_added) : null;

            $days = $baselineDate
                ? max(1, Carbon::parse($latest->log_date)->diffInDays($baselineDate))
                : 1;

            $gain = (float) $latest->weight - (float) $this->latest_weight;

            return $gain / $days;
        }

        return null;
    }

    public function getGrowthStatusAttribute()
    {
        $gain = $this->weight_gain;

        if ($gain === null) {
            return 'no_data';
        }

        if ($gain > 0) {
            return 'good';
        }

        if ($gain < 0) {
            return 'declining';
        }

        return 'stagnant';
    }
}
