<div class="overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm">
    <div class="min-w-0 bg-white">
        <div class="p-4 sm:p-6">
            @include('livewire.office-admin.partials.queue-dashboard-panel', [
                'showLiveMonitor' => true,
                'liveMonitorRoute' => $liveMonitorRoute,
                'liveMonitorLabel' => $liveMonitorLabel,
            ])
        </div>
    </div>
</div>
