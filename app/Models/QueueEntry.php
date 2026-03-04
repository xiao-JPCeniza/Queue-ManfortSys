<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueEntry extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_SERVING = 'serving';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NOT_SERVED = 'not_served';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'office_id',
        'queue_number',
        'status',
        'served_by',
        'served_at',
        'called_at',
    ];

    protected $casts = [
        'served_at' => 'datetime',
        'called_at' => 'datetime',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function servedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    public function scopeServing($query)
    {
        return $query->where('status', self::STATUS_SERVING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeNotServed($query)
    {
        return $query->where('status', self::STATUS_NOT_SERVED);
    }
}
