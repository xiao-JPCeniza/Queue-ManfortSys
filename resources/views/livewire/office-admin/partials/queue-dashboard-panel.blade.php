@php($showHrmoMonitor = $showHrmoMonitor ?? false)

<div class="grid grid-cols-1 xl:grid-cols-5 gap-6 items-start">
    <section class="xl:col-span-3 lgu-card overflow-hidden" aria-labelledby="serving-heading">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="serving-heading" class="lgu-section-title text-slate-800">Currently Serving</h2>
                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $serving ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                    {{ $serving ? 'Active ticket' : 'No active ticket' }}
                </span>
            </div>
            <p class="mt-2 text-xs text-slate-500">Call the next number, play voice announcement, and complete the current ticket.</p>
        </div>

        <div class="p-6">
            @if($serving)
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-5 py-6">
                    <p class="text-xs uppercase tracking-wide text-emerald-700">Ticket Number</p>
                    <p class="mt-2 text-5xl font-bold text-emerald-700 tracking-tight" aria-label="Current queue number {{ $serving->queue_number }}">
                        {{ $serving->queue_number }}
                    </p>
                    <p class="mt-2 text-sm text-emerald-700/80">Called at {{ $serving->called_at?->timezone('Asia/Manila')?->format('h:i A') }}</p>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">
                    <p class="text-slate-500">No one is being served right now.</p>
                </div>
            @endif

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button wire:click="callNext" type="button"
                        class="lgu-btn justify-center px-5 py-3 bg-emerald-600 text-white rounded-xl font-semibold text-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 shadow-sm">
                    Call Next
                </button>

                <button type="button"
                        onclick="window.callServingNumber(@js($serving?->queue_number), @js($office->name))"
                        @disabled(!$serving)
                        class="lgu-btn inline-flex items-center justify-center gap-2 px-4 py-3 bg-amber-500 text-slate-900 rounded-xl font-semibold text-sm hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5 6 9H3v6h3l5 4V5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a5 5 0 0 1 0 7" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 6a9 9 0 0 1 0 12" />
                    </svg>
                    Annouce Ticket
                </button>
            </div>

            @if($serving)
                <button wire:click="complete({{ $serving->id }})" type="button"
                        class="lgu-btn mt-3 w-full justify-center px-5 py-3 bg-slate-700 text-white rounded-xl font-medium text-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Mark completed
                </button>
            @endif
        </div>
    </section>

    <div class="xl:col-span-2 space-y-4">
        <section class="lgu-card p-5 border border-blue-100 bg-gradient-to-br from-blue-50 via-white to-emerald-50" aria-labelledby="quick-actions-heading">
            <h2 id="quick-actions-heading" class="lgu-section-title text-slate-800">Quick Actions</h2>
            <p class="mt-1 text-xs text-slate-500">Open related links for clients and staff.</p>

            <div class="mt-4 grid grid-cols-1 gap-2.5">
                @if($showHrmoMonitor)
                    <a href="{{ route('office.hrmo.monitor', $office->slug) }}"
                       target="_blank" rel="noopener noreferrer"
                       onclick="return confirm('Open HRMO Live Monitor in a new tab?');"
                       class="lgu-btn inline-flex items-center justify-center gap-2 px-4 py-2.5 text-white bg-emerald-600 rounded-xl font-medium text-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        Open HRMO Live Monitor
                    </a>
                @endif

                @if($office->slug === 'hrmo')
                    <button type="button"
                            wire:click="resetTickets"
                            wire:confirm="Reset all generated tickets for today? This will also clear waiting and serving entries."
                            wire:loading.attr="disabled"
                            wire:target="resetTickets"
                            class="lgu-btn inline-flex items-center justify-center gap-2 px-4 py-2.5 text-amber-800 bg-amber-50 rounded-xl font-medium text-sm border border-amber-200 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        Reset Tickets
                    </button>

                    <button type="button"
                            wire:click="clearTransaction"
                            wire:confirm="Clear all entries listed in Recent Transactions (Today)?"
                            wire:loading.attr="disabled"
                            wire:target="clearTransaction"
                            class="lgu-btn inline-flex items-center justify-center gap-2 px-4 py-2.5 text-red-700 bg-red-50 rounded-xl font-medium text-sm border border-red-200 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        Clear Transaction
                    </button>
                @endif
            </div>
        </section>

        <section class="lgu-card overflow-hidden" aria-labelledby="waiting-heading">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                <h2 id="waiting-heading" class="lgu-section-title text-slate-800">Waiting Line</h2>
                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    {{ $waiting->count() }} in queue
                </span>
            </div>

            <div class="max-h-80 overflow-y-auto">
                @forelse($waiting as $entry)
                    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between hover:bg-slate-50/70">
                        <div>
                            <span class="font-semibold text-slate-800">{{ $entry->queue_number }}</span>
                            <p class="text-xs text-slate-400 mt-0.5">Joined {{ $entry->created_at->timezone('Asia/Manila')->format('h:i A') }}</p>
                        </div>
                        <span class="text-xs font-medium text-slate-500">#{{ $loop->iteration }}</span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-slate-500 text-center text-sm">No one waiting.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
