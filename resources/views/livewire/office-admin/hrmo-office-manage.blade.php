<div wire:poll.5s="tick">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="lgu-page-title">HRMO Live Queue Monitor</h1>
            <p class="text-slate-600 text-sm mt-1">Real-time queue view with 1-minute auto-advance for currently served tickets.</p>
        </div>
        <a href="{{ route('office.dashboard', $office->slug) }}" class="lgu-btn inline-flex items-center gap-2 px-4 py-2.5 bg-slate-700 text-white rounded-xl hover:bg-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
            Back to Office Dashboard
        </a>
    </div>

    <section class="lgu-card p-6 mb-6" aria-labelledby="summary-heading">
        <h2 id="summary-heading" class="lgu-section-title mb-4">Overall Tickets Being Accommodated</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Today</p>
                <p class="text-3xl font-bold text-slate-800 mt-2">{{ $summary['total_today'] }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-wide text-emerald-700">Completed Today</p>
                <p class="text-3xl font-bold text-emerald-700 mt-2">{{ $summary['completed_today'] }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs uppercase tracking-wide text-blue-700">Active Now</p>
                <p class="text-3xl font-bold text-blue-700 mt-2">{{ $summary['active_now'] }}</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="lgu-card p-6 xl:col-span-1" aria-labelledby="now-serving-heading">
            <h2 id="now-serving-heading" class="lgu-section-title mb-4">Now Serving</h2>
            @if($serving)
                <p class="text-5xl font-bold text-emerald-600 tracking-tight">{{ $serving->queue_number }}</p>
                <p class="text-slate-500 text-sm mt-3">
                    Called at {{ $serving->called_at?->format('h:i:s A') }}
                </p>
                <p class="text-sm mt-2 {{ ($secondsLeft ?? 0) <= 15 ? 'text-red-600' : 'text-slate-600' }}">
                    Auto-next in {{ $secondsLeft ?? 0 }} second(s)
                </p>
            @else
                <p class="text-slate-500">No ticket currently being served.</p>
            @endif
        </section>

        <section class="lgu-card p-6 xl:col-span-1" aria-labelledby="next-inline-heading">
            <h2 id="next-inline-heading" class="lgu-section-title mb-4">Queue Next Inline</h2>
            @if($nextInline)
                <p class="text-5xl font-bold text-blue-700 tracking-tight">{{ $nextInline->queue_number }}</p>
                <p class="text-slate-500 text-sm mt-3">Queued at {{ $nextInline->created_at->format('h:i:s A') }}</p>
            @else
                <p class="text-slate-500">No waiting ticket in line.</p>
            @endif
        </section>

        <section class="lgu-card p-6 xl:col-span-1" aria-labelledby="recently-called-heading">
            <h2 id="recently-called-heading" class="lgu-section-title mb-4">Recently Called</h2>
            <div class="space-y-2 max-h-80 overflow-y-auto">
                @forelse($recentlyCalled as $entry)
                    <div class="rounded-lg border border-slate-200 px-3 py-2 flex items-center justify-between bg-slate-50">
                        <span class="font-semibold text-slate-800">{{ $entry->queue_number }}</span>
                        <span class="text-xs text-slate-500">{{ $entry->served_at?->format('h:i:s A') }}</span>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm">No recently called ticket yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="lgu-card p-6 mt-6" aria-labelledby="overall-activity-heading">
        <h2 id="overall-activity-heading" class="lgu-section-title mb-4">Overall Ticket Activity (Today)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-slate-200 text-slate-500">
                        <th class="py-2 pr-4 font-medium">Ticket #</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium">Issued</th>
                        <th class="py-2 pr-4 font-medium">Called</th>
                        <th class="py-2 pr-4 font-medium">Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overallTickets as $entry)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-semibold text-slate-800">{{ $entry->queue_number }}</td>
                            <td class="py-2 pr-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    {{ $entry->status === 'serving' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $entry->status === 'waiting' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $entry->status === 'completed' ? 'bg-slate-200 text-slate-700' : '' }}">
                                    {{ strtoupper($entry->status) }}
                                </span>
                            </td>
                            <td class="py-2 pr-4 text-slate-600">{{ $entry->created_at->format('h:i:s A') }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $entry->called_at?->format('h:i:s A') ?? '-' }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $entry->served_at?->format('h:i:s A') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">No tickets yet for HRMO today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

