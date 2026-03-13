<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueEntry extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_SERVING = 'serving';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NOT_SERVED = 'not_served';
    public const STATUS_CANCELLED = 'cancelled';
    public const TYPE_REGULAR = 'regular';
    public const TYPE_SENIOR_PREGNANT = 'senior_pregnant';

    protected $fillable = [
        'office_id',
        'queue_number',
        'client_type',
        'status',
        'served_by',
        'served_at',
        'called_at',
        'recent_transaction_cleared_at',
    ];

    protected $casts = [
        'served_at' => 'datetime',
        'called_at' => 'datetime',
        'recent_transaction_cleared_at' => 'datetime',
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

    public function scopeOrderedForService(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN client_type = ? THEN 0 ELSE 1 END', [self::TYPE_SENIOR_PREGNANT])
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public static function clientTypeOptions(): array
    {
        return [
            self::TYPE_REGULAR => [
                'label' => 'Regular',
                'description' => 'Standard queue ticket for the selected office.',
            ],
            self::TYPE_SENIOR_PREGNANT => [
                'label' => 'Senior / Pregnant',
                'description' => 'Priority assistance ticket for senior citizens and pregnant clients.',
            ],
        ];
    }

    public static function normalizeClientType(?string $clientType): string
    {
        return array_key_exists((string) $clientType, self::clientTypeOptions())
            ? (string) $clientType
            : self::TYPE_REGULAR;
    }

    public static function clientTypeLabel(?string $clientType): string
    {
        $normalizedType = self::normalizeClientType($clientType);

        return self::clientTypeOptions()[$normalizedType]['label'];
    }

    public function getClientTypeLabelAttribute(): string
    {
        return self::clientTypeLabel($this->client_type);
    }

    public function isPriorityClient(): bool
    {
        return self::normalizeClientType($this->client_type) === self::TYPE_SENIOR_PREGNANT;
    }

    public function serviceOrderKey(): string
    {
        return sprintf(
            '%d-%020d-%010d',
            $this->isPriorityClient() ? 0 : 1,
            $this->created_at?->getTimestamp() ?? 0,
            $this->id
        );
    }
}
