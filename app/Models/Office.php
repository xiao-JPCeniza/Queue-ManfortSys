<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Office extends Model
{
    public const MUNICIPALITY_QUEUE_SERVICE_SLUGS = [
        'hrmo',
        'treasury',
        'accounting',
        'civil-registry',
        'business-permits',
        'assessors-office',
        'mho',
        'mswdo',
    ];

    protected $fillable = [
        'name',
        'slug',
        'prefix',
        'description',
        'next_number',
        'tickets_accommodated_total',
        'is_active',
    ];

    protected $casts = [
        'tickets_accommodated_total' => 'integer',
        'is_active' => 'boolean',
    ];

    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class)->orderBy('created_at');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function waitingCount(): int
    {
        return $this->queueEntries()->where('status', 'waiting')->count();
    }

    public function getQueueJoinUrl(): string
    {
        return route('queue.join', ['office' => $this->slug]);
    }

    public function generateNextQueueNumber(): string
    {
        return DB::transaction(function (): string {
            $office = self::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $prefix = $office->resolveQueuePrefix();
            $manilaNow = now('Asia/Manila');
            $dbTimezone = (string) config('app.timezone', 'UTC');
            $dayStart = $manilaNow->copy()->startOfDay()->setTimezone($dbTimezone);
            $dayEnd = $manilaNow->copy()->endOfDay()->setTimezone($dbTimezone);

            $lastTodayQueueNumber = QueueEntry::query()
                ->where('office_id', $office->id)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->orderByDesc('id')
                ->value('queue_number');

            $nextNumber = 1;
            if (is_string($lastTodayQueueNumber) && preg_match('/-(\d+)$/', $lastTodayQueueNumber, $matches) === 1) {
                $nextNumber = ((int) $matches[1]) + 1;
            }

            $office->next_number = $nextNumber + 1;
            $office->save();
            $this->next_number = $office->next_number;

            return sprintf('%s-%03d', $prefix, $nextNumber);
        });
    }

    private function resolveQueuePrefix(): string
    {
        $prefix = strtoupper(trim((string) $this->prefix));

        // Keep HRMO ticket format stable even if old data has a wrong prefix.
        if ($this->slug === 'hrmo') {
            $prefix = 'HRMO';
        }

        if ($prefix === '') {
            $prefix = strtoupper(substr($this->slug, 0, 4));
        }

        return $prefix;
    }
}
