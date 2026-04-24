<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailAlertDelivery extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'fingerprint',
        'alert_type',
        'recipient',
        'subject',
        'status',
        'payload_json',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'sent_at' => 'datetime',
    ];

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function markAsSent(): void
    {
        $this->forceFill([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
        ])->save();
    }

    public function markAsFailed(?string $message = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'sent_at' => null,
            'error_message' => $message !== null ? Str::limit($message, 1000) : null,
        ])->save();
    }
}
