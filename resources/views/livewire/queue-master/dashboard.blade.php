<div>
    <div class="mb-8">
        <h1 class="lgu-page-title mb-1">Queue Master Dashboard</h1>
        <p class="text-slate-600 text-sm">Manage offices and monitor queue activity across the LGU.</p>
    </div>

    <section class="mb-8" aria-labelledby="offices-heading">
        <h2 id="offices-heading" class="lgu-section-title mb-4">Offices</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($offices as $office)
                <article class="lgu-card p-5 transition hover:shadow-md">
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-slate-800 text-lg">{{ $office->name }}</h3>
                            <p class="text-sm text-slate-500 mt-0.5">{{ $office->prefix }} • Next #{{ $office->next_number }}</p>
                            <p class="text-sm text-emerald-600 font-medium mt-2 inline-flex items-center gap-1">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                {{ $office->waiting_count }} waiting
                            </p>
                        </div>
                        <div class="flex flex-col gap-2 shrink-0">
                            <a href="{{ route('queue-master.office', $office->slug) }}"
                               class="lgu-btn px-3 py-2 bg-blue-800 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Manage
                            </a>
                            <button wire:click="resetNumbering({{ $office->id }})"
                                    wire:confirm="Reset queue numbering for {{ $office->name }} to 1?"
                                    class="lgu-btn px-3 py-2 text-amber-700 bg-amber-50 text-sm font-medium rounded-lg hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                Reset #
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="activity-heading">
        <h2 id="activity-heading" class="lgu-section-title mb-4">Recent queue activity</h2>
        <div class="lgu-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" role="table" aria-label="Recent queue entries">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Office</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Queue #</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Status</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentEntries as $entry)
                            <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                                <td class="px-4 py-3 text-slate-800">{{ $entry->office->name }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $entry->queue_number }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                        @if($entry->status === 'waiting') bg-amber-100 text-amber-800
                                        @elseif($entry->status === 'serving') bg-blue-100 text-blue-800
                                        @else bg-slate-100 text-slate-600 @endif">
                                        {{ $entry->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $entry->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No active queue entries.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
