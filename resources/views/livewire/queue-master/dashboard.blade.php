<div>
    @php
        $superAdminOfficeNames = [
            'accounting' => 'Accounting Office',
            'hrmo' => 'Human Resource',
            'mho' => 'Health Office',
            'treasury' => 'Treasury Office',
        ];
    @endphp

    <div class="mb-8">
        <h1 class="lgu-page-title mb-1">{{ auth()->user()?->isSuperAdmin() ? 'Super Admin Dashboard' : 'Dashboard' }}</h1>
        <p class="text-slate-600 text-sm">Monitor queue activity across all Offices.</p>
    </div>

    <section class="mb-8" aria-labelledby="offices-heading">
        <h2 id="offices-heading" class="lgu-section-title mb-4">Offices</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($offices as $office)
                @php($officeDisplayName = $superAdminOfficeNames[$office->slug] ?? $office->name)
                <article class="lgu-card p-5 transition hover:shadow-md">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-lg font-semibold text-slate-800">{{ $officeDisplayName }}</h3>

                            <div class="flex shrink-0 flex-wrap gap-2">
                                <a href="{{ route('queue-master.office', $office->slug) }}"
                                   class="lgu-btn rounded-lg bg-blue-800 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Manage
                                </a>
                                <button wire:click="resetNumbering({{ $office->id }})"
                                        wire:confirm="Reset queue numbering for {{ $officeDisplayName }} to 1? This will clear this office's generated tickets for today."
                                        class="lgu-btn rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                    Reset #
                                </button>
                            </div>
                        </div>

                        <p class="mt-0.5 text-sm text-slate-500">{{ $office->prefix }} | Next #{{ $office->next_number }}</p>

                        <div class="mt-4 grid grid-cols-1 gap-3 xl:grid-cols-2">
                            <section class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Serving</p>
                                <p class="mt-1 text-xl font-bold leading-none text-emerald-700">
                                    {{ $office->serving_ticket ?: 'No active ticket' }}
                                </p>
                            </section>

                            <section class="rounded-xl border border-amber-100 bg-amber-50/80 px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">Next-in Line</p>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700">
                                        <span class="inline-block h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                        {{ $office->waiting_count }} Queue
                                    </span>
                                </div>
                                <p class="mt-1 text-xl font-bold leading-none text-amber-700">
                                    {{ $office->next_waiting_ticket ?: 'No one waiting' }}
                                </p>
                            </section>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="activity-heading">
        <h2 id="activity-heading" class="lgu-section-title mb-4">Recent Queue Activity</h2>
        <div class="lgu-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" role="table" aria-label="Recent queue entries">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Office</th>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Queue #</th>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                            <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-700">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentEntries as $entry)
                            @php($entryOfficeDisplayName = $superAdminOfficeNames[$entry->office->slug] ?? $entry->office->name)
                            <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                                <td class="px-4 py-3 text-slate-800">{{ $entryOfficeDisplayName }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $entry->queue_number }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium
                                        @if($entry->status === 'waiting') bg-amber-100 text-amber-800
                                        @elseif($entry->status === 'serving') bg-blue-100 text-blue-800
                                        @elseif($entry->status === 'completed') bg-emerald-100 text-emerald-800
                                        @elseif($entry->status === 'not_served') bg-rose-100 text-rose-800
                                        @else bg-slate-100 text-slate-600 @endif">
                                        {{ \Illuminate\Support\Str::headline($entry->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ ($entry->activityAt ?? $entry->created_at)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                    {{ auth()->user()?->isSuperAdmin() ? 'No queue activity yet today.' : 'No active queue entries.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
