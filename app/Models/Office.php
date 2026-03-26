<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Office extends Model
{
    public const TREASURY_DEFAULT_SERVICE_WINDOW_LABELS = [
        1 => 'Teller 1',
        2 => 'Teller 2',
        3 => 'Teller 3',
        4 => 'Teller 4',
        5 => 'Teller 5',
        6 => 'Teller 6',
        7 => 'Teller 7',
        8 => 'Teller 8',
        9 => 'Teller 9',
        10 => 'Window 1',
        11 => 'Window 2',
    ];

    public const TREASURY_QUEUE_SERVICE_OPTIONS = [
        'business_taxes_fees_charges' => [
            'label' => 'Business Taxes, Fees and Charges',
            'description' => 'For transactions handled by Teller 1 to Teller 3.',
            'destination' => 'Teller 1-3',
            'window_numbers' => [1, 2, 3],
        ],
        'real_property_taxes' => [
            'label' => 'Real Property Taxes',
            'description' => 'For transactions handled by Teller 4, Teller 8, and Teller 9.',
            'destination' => 'Teller 4, Teller 8, and Teller 9',
            'window_numbers' => [4, 8, 9],
        ],
        'marriage_license' => [
            'label' => 'Marriage License',
            'description' => 'For transactions handled by Teller 5.',
            'destination' => 'Teller 5',
            'window_numbers' => [5],
        ],
        'market_charges' => [
            'label' => 'Market Charges',
            'description' => 'For transactions handled by Teller 6 and Teller 7.',
            'destination' => 'Teller 6-7',
            'window_numbers' => [6, 7],
        ],
        'release_of_disbursement_of_cash' => [
            'label' => 'Release of Disbursement of Cash',
            'description' => 'For transactions handled at Window 1.',
            'destination' => 'Window 1',
            'window_numbers' => [10],
        ],
        'issuance_and_releasing_of_check' => [
            'label' => 'Issuance and Releasing of Check',
            'description' => 'For transactions handled at Window 2.',
            'destination' => 'Window 2',
            'window_numbers' => [11],
        ],
    ];

    public const MUNICIPALITY_QUEUE_SERVICE_SLUGS = [
        'hrmo',
        'treasury',
        'mto',
        'accounting',
        'civil-registry',
        'business-permits',
        'assessors-office',
        'menro',
        'mho',
        'mswdo',
    ];

    public const DISPLAY_NAME_MAP = [
        'accounting' => 'Municipal Accounting Office',
        'assessors-office' => "Municipal Assessor's Office",
        'business-permits' => 'Business Permits and Licensing Office',
        'civil-registry' => 'Local Civil Registry Office',
        'hrmo' => 'Human Resource Management Office',
        'menro' => 'Municipal Environment and Natural Resources Office',
        'mho' => 'Municipal Health Office',
        'mto' => "Municipal Treasurer's Office",
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
        'service_window_count',
        'service_window_labels',
        'tickets_accommodated_total',
        'is_active',
        'show_in_public_queue',
    ];

    protected $casts = [
        'service_window_count' => 'integer',
        'service_window_labels' => 'array',
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

    public static function resolveSuperAdminContextOffice(): ?self
    {
        $hrmoOffice = self::query()
            ->where('slug', 'hrmo')
            ->first();

        if ($hrmoOffice) {
            return $hrmoOffice;
        }

        $publicQueueOffice = self::sortPublicQueueOffices(
            self::query()
                ->activePublicQueue()
                ->get()
        )->first();

        if ($publicQueueOffice) {
            return $publicQueueOffice;
        }

        return self::query()
            ->orderBy('name')
            ->first();
    }

    public function getDisplayNameAttribute(): string
    {
        return self::DISPLAY_NAME_MAP[$this->slug]
            ?? trim((string) $this->description)
            ?: $this->name;
    }

    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function waitingCount(): int
    {
        return $this->queueEntries()->where('status', 'waiting')->count();
    }

    public function resolvedServiceWindowCount(): int
    {
        return max(1, (int) ($this->service_window_count ?? 1));
    }

    public function usesMultipleServiceWindows(): bool
    {
        return $this->resolvedServiceWindowCount() > 1;
    }

    public function serviceWindowNumbers(): Collection
    {
        return collect(range(1, $this->resolvedServiceWindowCount()));
    }

    public function serviceWindowLabel(int $windowNumber): string
    {
        $windowNumber = max(1, $windowNumber);
        $customLabel = $this->sanitizeServiceWindowLabels($this->service_window_labels ?? [])
            [$windowNumber] ?? null;

        if ($customLabel !== null) {
            return $customLabel;
        }

        $defaultLabel = $this->defaultServiceWindowLabels()[$windowNumber] ?? null;

        if ($defaultLabel !== null) {
            return $defaultLabel;
        }

        return 'Window '.$windowNumber;
    }

    public function serviceWindowDisplayTitle(int $windowNumber): string
    {
        $windowNumber = max(1, $windowNumber);
        $serviceKeys = $this->queueServiceKeysForWindow($windowNumber);

        if (count($serviceKeys) === 1) {
            return $this->queueServiceLabel($serviceKeys[0]) ?? $this->serviceWindowLabel($windowNumber);
        }

        return $this->serviceWindowLabel($windowNumber);
    }

    public function editableServiceWindowLabels(): Collection
    {
        $customLabels = $this->sanitizeServiceWindowLabels($this->service_window_labels ?? []);

        return $this->serviceWindowNumbers()
            ->mapWithKeys(fn (int $windowNumber) => [
                (string) $windowNumber => $customLabels[$windowNumber] ?? '',
            ]);
    }

    public function sanitizeServiceWindowLabels(array $labels, ?int $maxWindowCount = null): array
    {
        $maxWindowCount ??= $this->resolvedServiceWindowCount();
        $normalizedLabels = [];

        foreach ($labels as $windowNumber => $label) {
            $windowNumber = (int) $windowNumber;

            if ($windowNumber < 1 || $windowNumber > $maxWindowCount) {
                continue;
            }

            $normalizedLabel = trim((string) $label);

            if ($normalizedLabel === '') {
                continue;
            }

            $normalizedLabels[$windowNumber] = Str::limit($normalizedLabel, 40, '');
        }

        ksort($normalizedLabels);

        return $normalizedLabels;
    }

    public function defaultServiceWindowLabels(): array
    {
        if (
            $this->usesTreasuryRouting()
            && $this->resolvedServiceWindowCount() >= count(self::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS)
        ) {
            return self::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS;
        }

        return [];
    }

    public function queueServiceOptions(): array
    {
        if (
            $this->usesTreasuryRouting()
            && $this->resolvedServiceWindowCount() >= count(self::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS)
        ) {
            return self::TREASURY_QUEUE_SERVICE_OPTIONS;
        }

        return [];
    }

    public function hasQueueServiceOptions(): bool
    {
        return $this->queueServiceOptions() !== [];
    }

    public function queueServiceOption(?string $serviceKey): ?array
    {
        if (! is_string($serviceKey) || trim($serviceKey) === '') {
            return null;
        }

        $normalizedKey = trim($serviceKey);

        return $this->queueServiceOptions()[$normalizedKey] ?? null;
    }

    public function queueServiceLabel(?string $serviceKey): ?string
    {
        return $this->queueServiceOption($serviceKey)['label'] ?? null;
    }

    public function queueServiceDestinationLabel(?string $serviceKey): ?string
    {
        return $this->queueServiceOption($serviceKey)['destination'] ?? null;
    }

    public function queueServiceWindowNumbers(?string $serviceKey): array
    {
        $windowNumbers = $this->queueServiceOption($serviceKey)['window_numbers'] ?? [];

        return collect($windowNumbers)
            ->map(fn ($windowNumber) => max(1, (int) $windowNumber))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function queueServiceKeysForWindow(int $windowNumber): array
    {
        $windowNumber = max(1, $windowNumber);

        return collect($this->queueServiceOptions())
            ->filter(fn (array $serviceOption) => in_array($windowNumber, $serviceOption['window_numbers'] ?? [], true))
            ->keys()
            ->values()
            ->all();
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

    private function usesTreasuryRouting(): bool
    {
        return in_array($this->slug, ['treasury', 'mto'], true);
    }
}
