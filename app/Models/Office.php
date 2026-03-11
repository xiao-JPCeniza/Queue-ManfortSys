<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
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

    public const DISPLAY_NAME_MAP = [
        'accounting' => 'Municipal Accounting Office',
        'assessors-office' => "Municipal Assessor's Office",
        'business-permits' => 'Business Permits and Licensing Office',
        'civil-registry' => 'Local Civil Registry Office',
        'hrmo' => 'Human Resource Management Office',
        'mho' => 'Municipal Health Office',
        'mswdo' => 'Municipal Social Welfare and Development Office',
        'obo' => 'Office of the Building Official',
        'treasury' => "Municipal Treasurer's Office",
    ];

    protected $fillable = [
        'name',
        'slug',
        'prefix',
        'description',
        'next_number',
        'tickets_accommodated_total',
        'is_active',
        'show_in_public_queue',
    ];

    protected $casts = [
        'tickets_accommodated_total' => 'integer',
        'is_active' => 'boolean',
        'show_in_public_queue' => 'boolean',
    ];

    public function scopePublicQueueVisible(Builder $query): Builder
    {
        return $query->where('show_in_public_queue', true);
    }

    public function scopeActivePublicQueue(Builder $query): Builder
    {
        return $query->where('is_active', true)->publicQueueVisible();
    }

    public static function sortPublicQueueOffices(iterable $offices): Collection
    {
        $orderMap = array_flip(self::MUNICIPALITY_QUEUE_SERVICE_SLUGS);
        $collection = $offices instanceof Collection ? $offices : collect($offices);

        return $collection
            ->sortBy(function (Office $office) use ($orderMap) {
                $legacyOrder = $orderMap[$office->slug] ?? null;

                return sprintf(
                    '%d-%05d-%s',
                    $legacyOrder === null ? 1 : 0,
                    $legacyOrder ?? 99999,
                    strtolower((string) $office->name)
                );
            })
            ->values();
    }

    public function getDisplayNameAttribute(): string
    {
        return self::DISPLAY_NAME_MAP[$this->slug]
            ?? trim((string) $this->description)
            ?: $this->name;
    }

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
