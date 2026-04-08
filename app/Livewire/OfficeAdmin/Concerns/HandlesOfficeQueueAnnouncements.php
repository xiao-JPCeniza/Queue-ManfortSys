<?php

namespace App\Livewire\OfficeAdmin\Concerns;

use App\Models\Office;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait HandlesOfficeQueueAnnouncements
{
    protected function storeOfficeAnnouncement(Office $office, string $type, string $queueNumber, ?int $serviceWindowNumber = null): void
    {
        Cache::put(
            $this->officeAnnouncementCacheKey($office),
            [
                'id' => (string) Str::uuid(),
                'type' => $type,
                'queue_number' => $queueNumber,
                'service_window_number' => $serviceWindowNumber,
                'service_window_label' => $serviceWindowNumber === null ? null : $office->serviceWindowAnnouncementLabel($serviceWindowNumber),
                'triggered_at' => now()->toIso8601String(),
            ],
            now()->addMinutes(30)
        );
    }

    protected function getOfficeAnnouncement(Office $office): ?array
    {
        $payload = Cache::get($this->officeAnnouncementCacheKey($office));

        return is_array($payload) ? $payload : null;
    }

    protected function officeAnnouncementCacheKey(Office $office): string
    {
        return 'office-queue-announcement:'.$office->getKey();
    }
}
