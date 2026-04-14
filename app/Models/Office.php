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
    public const BPLO_DEFAULT_SERVICE_WINDOW_LABELS = [
        1 => 'Business Permit Application',
        2 => 'Request for Certifications',
    ];

    public const BPLO_QUEUE_SERVICE_OPTIONS = [
        'business_permit_application' => [
            'label' => 'Business Permit Application (New & Renewal)',
            'description' => 'For new and renewal business permit applications handled at Window 1.',
            'destination' => 'Window 1',
            'window_numbers' => [1],
        ],
        'request_for_certifications' => [
            'label' => 'Request for Certifications',
            'description' => 'For certification requests handled at Window 2.',
            'destination' => 'Window 2',
            'window_numbers' => [2],
        ],
    ];

    public const HRMO_DEFAULT_SERVICE_WINDOW_LABELS = [
        1 => 'Recruitment and Selection Services',
        2 => 'Certifications and Service Record',
        3 => 'Valid Identification Card',
        4 => 'ARTA Identification Card',
    ];

    public const HRMO_QUEUE_SERVICE_OPTIONS = [
        'recruitment_selection_services' => [
            'label' => 'Request for Recruitment and Selection Services',
            'description' => 'For recruitment and selection service requests handled at Window 1.',
            'destination' => 'Window 1',
            'window_numbers' => [1],
        ],
        'certifications_service_record' => [
            'label' => 'Request for Certifications and Service Record',
            'description' => 'For certification and service record requests handled at Window 2.',
            'destination' => 'Window 2',
            'window_numbers' => [2],
        ],
        'valid_identification_card' => [
            'label' => 'Request for Valid Identification Card',
            'description' => 'For valid identification card requests handled at Window 3.',
            'destination' => 'Window 3',
            'window_numbers' => [3],
        ],
        'arta_identification_card' => [
            'label' => 'Request for Anti-Red Tape Act (ARTA) Identification Card',
            'description' => 'For ARTA identification card requests handled at Window 4.',
            'destination' => 'Window 4',
            'window_numbers' => [4],
        ],
    ];

    public const MENRO_DEFAULT_SERVICE_WINDOW_LABELS = [
        1 => 'Addressing Environmental Concerns',
        2 => 'Issuance of CLIVE Card',
        3 => 'Application for Environmental Clearance',
    ];

    public const MENRO_QUEUE_SERVICE_OPTIONS = [
        'addressing_environmental_concerns' => [
            'label' => 'Addressing Environmental Concerns',
            'description' => 'For environmental concerns handled at Window 1.',
            'destination' => 'Window 1',
            'window_numbers' => [1],
        ],
        'issuance_of_clive_card' => [
            'label' => 'Issuance of CLIVE Card',
            'description' => 'For CLIVE card issuance handled at Window 2.',
            'destination' => 'Window 2',
            'window_numbers' => [2],
        ],
        'application_for_environmental_clearance' => [
            'label' => 'Application for Environmental Clearance',
            'description' => 'For environmental clearance applications handled at Window 3.',
            'destination' => 'Window 3',
            'window_numbers' => [3],
        ],
    ];

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

    public const CIVIL_REGISTRY_DEFAULT_SERVICE_WINDOW_LABELS = [
        1 => 'Window 1-A',
        2 => 'Window 1-B',
        3 => 'Window 2',
        4 => 'Window 3',
        5 => 'Window 4-A',
        6 => 'Window 4-B',
    ];

    public const CIVIL_REGISTRY_QUEUE_SERVICE_OPTIONS = [
        'window_1_a' => [
            'label' => 'Birth Registration',
            'description' => 'Birth Registration (Current), Issuance of Birth Form 1A True Copy, Issuance of Birth Certified True Copy.',
            'destination' => 'Window 1-A',
            'window_numbers' => [1],
        ],
        'window_1_b' => [
            'label' => 'Birth Registration',
            'description' => 'Birth Registration (Current & Delayed), Issuance of Birth Form 1A True Copy, Issuance of Birth Certified True Copy, Legitimation.',
            'destination' => 'Window 1-B',
            'window_numbers' => [2],
        ],
        'window_2' => [
            'label' => 'Marriage Registration',
            'description' => 'Marriage Registration (Current & Delayed), Application of Marriage License, Issuance of Marriage Form 3A True Copy, Issuance of Marriage Certified True Copy.',
            'destination' => 'Window 2',
            'window_numbers' => [3],
        ],
        'window_3' => [
            'label' => 'Death Registration',
            'description' => 'Death Registration (Current & Delayed), Issuance of Death Form 2A True Copy, Issuance of Death Certified True Copy, PSA Request, PSA Releasing.',
            'destination' => 'Window 3',
            'window_numbers' => [4],
        ],
        'window_4_a' => [
            'label' => 'PSA Request',
            'description' => 'Correction of Clerical Error, Change of First Name.',
            'destination' => 'Window 4-A',
            'window_numbers' => [5],
        ],
        'window_4_b' => [
            'label' => 'Correction of Clerical Error',
            'description' => 'Change of Sex, Correction of Date of Birth, Court Order, Supplemental.',
            'destination' => 'Window 4-B',
            'window_numbers' => [6],
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

    public const DISPLAY_DESCRIPTION_MAP = [
        'civil-registry' => 'Municipal Local Civil Registry Office',
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

    public function getDisplayDescriptionAttribute(): string
    {
        return self::DISPLAY_DESCRIPTION_MAP[$this->slug]
            ?? trim((string) $this->description)
            ?: $this->display_name;
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

    public function accessibleServiceWindowCount(): int
    {
        if (! $this->exists) {
            return $this->resolvedServiceWindowCount();
        }

        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        $highestServingWindowNumber = QueueEntry::query()
            ->where('office_id', $this->getKey())
            ->serving()
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->max(DB::raw('COALESCE(service_window_number, 1)'));

        return max(
            $this->resolvedServiceWindowCount(),
            max(1, (int) $highestServingWindowNumber)
        );
    }

    public function accessibleServiceWindowNumbers(): Collection
    {
        return collect(range(1, $this->accessibleServiceWindowCount()));
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

    public function serviceWindowAnnouncementLabel(int $windowNumber): string
    {
        $windowNumber = max(1, $windowNumber);
        $label = $this->serviceWindowLabel($windowNumber);

        if ($this->isPhysicalServiceWindowLabel($label)) {
            return $label;
        }

        return 'Window '.$windowNumber;
    }

    public function editableServiceWindowLabels(): Collection
    {
        return $this->serviceWindowNumbers()
            ->mapWithKeys(fn (int $windowNumber) => [
                (string) $windowNumber => $this->serviceWindowLabel($windowNumber),
            ]);
    }

    public function alignedServiceWindowLabels(?int $windowCount = null): array
    {
        $windowCount = max(1, (int) ($windowCount ?? $this->resolvedServiceWindowCount()));
        $customLabels = $this->sanitizeServiceWindowLabels($this->service_window_labels ?? [], $windowCount);
        $defaultLabels = collect($this->configuredDefaultServiceWindowLabels())
            ->mapWithKeys(fn (string $label, int|string $windowNumber) => [(int) $windowNumber => $label])
            ->all();

        return collect(range(1, $windowCount))
            ->mapWithKeys(function (int $windowNumber) use ($customLabels, $defaultLabels): array {
                return [
                    $windowNumber => $customLabels[$windowNumber]
                        ?? $defaultLabels[$windowNumber]
                        ?? 'Window '.$windowNumber,
                ];
            })
            ->all();
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
        return collect($this->configuredDefaultServiceWindowLabels())
            ->mapWithKeys(fn (string $label, int|string $windowNumber) => [(int) $windowNumber => $label])
            ->filter(fn (string $label, int $windowNumber) => $windowNumber <= $this->resolvedServiceWindowCount())
            ->all();
    }

    public function queueServiceOptions(): array
    {
        $configuredOptions = collect($this->configuredQueueServiceOptions())
            ->mapWithKeys(function (array $serviceOption, string $serviceKey): array {
                $windowNumbers = $this->availableServiceWindowNumbers($serviceOption['window_numbers'] ?? []);

                if ($windowNumbers === []) {
                    return [];
                }

                $label = $this->synchronizeServiceOptionLabel($serviceOption, $windowNumbers);
                $destination = $this->formatServiceWindowDestination($windowNumbers);

                return [
                    $serviceKey => array_merge($serviceOption, [
                        'label' => $label,
                        'description' => $this->synchronizeServiceOptionDescription($serviceOption, $windowNumbers, $destination),
                        'destination' => $destination,
                        'window_numbers' => $windowNumbers,
                    ]),
                ];
            })
            ->all();

        $coveredWindowNumbers = collect($configuredOptions)
            ->flatMap(fn (array $serviceOption) => $serviceOption['window_numbers'] ?? [])
            ->map(fn ($windowNumber) => max(1, (int) $windowNumber))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return collect(array_merge(
            $configuredOptions,
            $this->fallbackQueueServiceOptions($coveredWindowNumbers)
        ))
            ->sortBy(fn (array $serviceOption, string $serviceKey) => sprintf(
                '%03d-%s',
                min($serviceOption['window_numbers'] ?? [999]),
                $serviceKey
            ))
            ->all();
    }

    public function hasQueueServiceOptions(): bool
    {
        return $this->queueServiceOptions() !== [];
    }

    public function hasConfiguredQueueServiceOptions(): bool
    {
        return $this->configuredQueueServiceOptions() !== [];
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

    public function queueRoutingServiceKeysForWindow(int $windowNumber): array
    {
        $windowNumber = max(1, $windowNumber);
        $serviceKeys = $this->queueServiceKeysForWindow($windowNumber);
        $sharedServiceKeys = $this->sharedTreasuryFrontlineRoutingServiceKeys();

        if (
            $sharedServiceKeys === []
            || ! collect($serviceKeys)->contains(fn (string $serviceKey) => in_array($serviceKey, $sharedServiceKeys, true))
        ) {
            return $serviceKeys;
        }

        return $sharedServiceKeys;
    }

    public function getQueueJoinUrl(): string
    {
        return route('queue.join', ['office' => $this->slug]);
    }

    public function queuePrefix(): string
    {
        return $this->resolveQueuePrefix();
    }

    public function generateNextQueueNumber(): string
    {
        return DB::transaction(function (): string {
            $office = self::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $prefix = $office->queuePrefix();
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

        // Keep Civil Registry ticket format aligned with the MCR abbreviation.
        if ($this->usesCivilRegistryRouting()) {
            $prefix = 'MCR';
        }

        if ($prefix === '') {
            $prefix = strtoupper(substr($this->slug, 0, 4));
        }

        return $prefix;
    }

    private function configuredDefaultServiceWindowLabels(): array
    {
        if ($this->usesBploRouting()) {
            return self::BPLO_DEFAULT_SERVICE_WINDOW_LABELS;
        }

        if ($this->usesHrmoRouting()) {
            return self::HRMO_DEFAULT_SERVICE_WINDOW_LABELS;
        }

        if ($this->usesMenroRouting()) {
            return self::MENRO_DEFAULT_SERVICE_WINDOW_LABELS;
        }

        if ($this->usesTreasuryRouting()) {
            return self::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS;
        }

        if ($this->usesCivilRegistryRouting()) {
            return self::CIVIL_REGISTRY_DEFAULT_SERVICE_WINDOW_LABELS;
        }

        return [];
    }

    private function configuredQueueServiceOptions(): array
    {
        $options = [];

        if ($this->usesBploRouting()) {
            $options = self::BPLO_QUEUE_SERVICE_OPTIONS;
        }

        if ($options === [] && $this->usesHrmoRouting()) {
            $options = self::HRMO_QUEUE_SERVICE_OPTIONS;
        }

        if ($options === [] && $this->usesMenroRouting()) {
            $options = self::MENRO_QUEUE_SERVICE_OPTIONS;
        }

        if ($options === [] && $this->usesTreasuryRouting()) {
            $options = self::TREASURY_QUEUE_SERVICE_OPTIONS;
        }

        if ($options === [] && $this->usesCivilRegistryRouting()) {
            $options = self::CIVIL_REGISTRY_QUEUE_SERVICE_OPTIONS;
        }

        if (! $this->hasCompleteConfiguredQueueRouting($options)) {
            return [];
        }

        return $options;
    }

    private function fallbackQueueServiceOptions(array $coveredWindowNumbers): array
    {
        if ($coveredWindowNumbers === [] && ! $this->usesMultipleServiceWindows()) {
            return [];
        }

        return $this->serviceWindowNumbers()
            ->reject(fn (int $windowNumber) => in_array($windowNumber, $coveredWindowNumbers, true))
            ->mapWithKeys(function (int $windowNumber): array {
                $windowLabel = $this->serviceWindowLabel($windowNumber);

                return [
                    'service_window_'.$windowNumber => [
                        'label' => $windowLabel,
                        'description' => 'For transactions handled at '.$windowLabel.'.',
                        'destination' => $windowLabel,
                        'window_numbers' => [$windowNumber],
                    ],
                ];
            })
            ->all();
    }

    private function availableServiceWindowNumbers(array $windowNumbers): array
    {
        return collect($windowNumbers)
            ->map(fn ($windowNumber) => max(1, (int) $windowNumber))
            ->filter(fn (int $windowNumber) => $windowNumber <= $this->resolvedServiceWindowCount())
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function formatServiceWindowDestination(array $windowNumbers): string
    {
        $labels = collect($windowNumbers)
            ->map(fn (int $windowNumber) => $this->serviceWindowLabel($windowNumber))
            ->values()
            ->all();

        return match (count($labels)) {
            0 => '',
            1 => $labels[0],
            2 => $labels[0].' and '.$labels[1],
            default => collect($labels)
                ->slice(0, -1)
                ->implode(', ')
                .', and '
                .$labels[array_key_last($labels)],
        };
    }

    private function synchronizeServiceOptionLabel(array $serviceOption, array $windowNumbers): string
    {
        $configuredLabel = trim((string) ($serviceOption['label'] ?? ''));

        if (count($windowNumbers) !== 1) {
            return $configuredLabel !== ''
                ? $configuredLabel
                : $this->serviceWindowLabel($windowNumbers[0] ?? 1);
        }

        $windowNumber = $windowNumbers[0];
        $currentWindowLabel = $this->serviceWindowLabel($windowNumber);
        $defaultWindowLabel = $this->configuredDefaultServiceWindowLabel($windowNumber);

        if ($defaultWindowLabel !== null && $currentWindowLabel !== $defaultWindowLabel) {
            return $currentWindowLabel;
        }

        return $configuredLabel !== ''
            ? $configuredLabel
            : $currentWindowLabel;
    }

    private function synchronizeServiceOptionDescription(array $serviceOption, array $windowNumbers, string $destination): string
    {
        $description = trim((string) ($serviceOption['description'] ?? ''));
        $configuredWindowNumbers = $this->normalizeServiceWindowNumbers($serviceOption['window_numbers'] ?? []);
        $configuredDestination = trim((string) ($serviceOption['destination'] ?? ''));

        if (
            $description !== ''
            && $windowNumbers === $configuredWindowNumbers
            && ($configuredDestination === '' || $destination === $configuredDestination)
        ) {
            return $description;
        }

        return 'For transactions handled at '.$destination.'.';
    }

    private function configuredDefaultServiceWindowLabel(int $windowNumber): ?string
    {
        $windowNumber = max(1, $windowNumber);

        return collect($this->configuredDefaultServiceWindowLabels())
            ->mapWithKeys(fn (string $label, int|string $configuredWindowNumber) => [(int) $configuredWindowNumber => $label])
            ->get($windowNumber);
    }

    private function normalizeServiceWindowNumbers(array $windowNumbers): array
    {
        return collect($windowNumbers)
            ->map(fn ($windowNumber) => max(1, (int) $windowNumber))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function hasCompleteConfiguredQueueRouting(array $options): bool
    {
        if ($options === []) {
            return true;
        }

        $highestRequiredWindowNumber = collect($options)
            ->flatMap(fn (array $serviceOption) => $serviceOption['window_numbers'] ?? [])
            ->map(fn ($windowNumber) => max(1, (int) $windowNumber))
            ->max();

        if (! is_int($highestRequiredWindowNumber)) {
            return true;
        }

        return $highestRequiredWindowNumber <= $this->resolvedServiceWindowCount();
    }

    private function isPhysicalServiceWindowLabel(string $label): bool
    {
        $normalizedLabel = trim($label);

        if ($normalizedLabel === '') {
            return false;
        }

        return preg_match('/^(window|teller|counter|desk|booth|lane|cashier|room|station|cubicle|area|bay)\b/i', $normalizedLabel) === 1;
    }

    private function sharedTreasuryFrontlineRoutingServiceKeys(): array
    {
        if (! $this->usesTreasuryRouting()) {
            return [];
        }

        $sharedLabels = [
            'Business Taxes, Fees and Charges',
            'Real Property Taxes',
            'Marriage License',
            'Market Charges',
        ];

        return collect($this->queueServiceOptions())
            ->filter(fn (array $serviceOption) => in_array((string) ($serviceOption['label'] ?? ''), $sharedLabels, true))
            ->keys()
            ->values()
            ->all();
    }

    private function usesTreasuryRouting(): bool
    {
        return in_array($this->slug, ['treasury', 'mto'], true);
    }

    private function usesBploRouting(): bool
    {
        return in_array($this->slug, ['business-permits', 'bplo'], true);
    }

    private function usesHrmoRouting(): bool
    {
        return $this->slug === 'hrmo';
    }

    private function usesMenroRouting(): bool
    {
        return $this->slug === 'menro';
    }

    private function usesCivilRegistryRouting(): bool
    {
        return in_array($this->slug, ['civil-registry', 'cr'], true);
    }

    private function manilaDayBounds(): array
    {
        $manilaNow = now('Asia/Manila');
        $dbTimezone = (string) config('app.timezone', 'UTC');

        return [
            $manilaNow->copy()->startOfDay()->setTimezone($dbTimezone),
            $manilaNow->copy()->endOfDay()->setTimezone($dbTimezone),
        ];
    }
}
