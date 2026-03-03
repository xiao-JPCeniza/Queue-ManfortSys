<div wire:poll.5s="tick" class="h-full overflow-auto bg-slate-100">
    <div class="mx-auto min-h-full w-full max-w-7xl p-4 sm:p-6 lg:p-8">
        <section class="lgu-card overflow-hidden border-2 border-blue-100">
            <header class="bg-blue-800 px-5 py-4 text-white sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Live Queue Monitor</h1>
                    </div>
                </div>
            </header>

            <div class="space-y-5 p-4 sm:p-6">
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-5">
                    <section class="xl:col-span-3 rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-100/50 p-4 sm:p-5" aria-labelledby="now-serving-heading">
                        <div class="flex items-center justify-between gap-2">
                            <h2 id="now-serving-heading" class="text-base font-semibold text-emerald-900">Serving Now</h2>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $serving ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $serving ? 'Active' : 'Idle' }}
                            </span>
                        </div>

                        @if($serving)
                            <div class="mt-4 rounded-2xl border border-emerald-200 bg-white p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-700">Queue Number</p>
                                <p class="mt-2 text-center text-6xl font-extrabold leading-none tracking-tight text-emerald-700 sm:text-7xl" aria-live="polite">{{ $serving->queue_number }}</p>

                                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                    <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                        Called at
                                        <p class="font-semibold text-slate-700">{{ $serving->called_at?->format('h:i:s A') }}</p>
                                    </div>
                                    <div class="rounded-lg px-3 py-2 text-sm font-semibold {{ ($secondsLeft ?? 0) <= 15 ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-sky-700' }}">
                                        Auto-next
                                        <p>{{ $secondsLeft ?? 0 }} second(s)</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 flex min-h-[200px] items-center justify-center rounded-2xl border border-dashed border-emerald-300 bg-white/70 px-6 py-8 text-center">
                                <div>
                                    <p class="text-xl font-semibold text-slate-700">No active ticket right now</p>
                                    <p class="mt-2 text-sm text-slate-500">The next waiting ticket will be called automatically.</p>
                                </div>
                            </div>
                        @endif
                    </section>

                    <section class="xl:col-span-2 rounded-2xl border border-sky-200 bg-gradient-to-br from-sky-50 via-white to-cyan-100/50 p-4 sm:p-5" aria-labelledby="next-inline-heading">
                        <div class="flex items-center justify-between gap-2">
                            <h2 id="next-inline-heading" class="text-base font-semibold text-sky-900">Next in Line</h2>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $nextInline ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $nextInline ? 'Queued' : 'Empty' }}
                            </span>
                        </div>

                        @if($nextInline)
                            <div class="mt-4 rounded-2xl border border-sky-200 bg-white p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-sky-700">Upcoming Ticket</p>
                                <p class="mt-2 text-center text-5xl font-extrabold leading-none tracking-tight text-sky-700 sm:text-6xl" aria-live="polite">{{ $nextInline->queue_number }}</p>
                                <div class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                    Queued at
                                    <p class="font-semibold text-slate-700">{{ $nextInline->created_at->format('h:i:s A') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 flex min-h-[200px] items-center justify-center rounded-2xl border border-dashed border-sky-300 bg-white/70 px-6 py-8 text-center">
                                <div>
                                    <p class="text-xl font-semibold text-slate-700">No waiting ticket in line</p>
                                    <p class="mt-2 text-sm text-slate-500">New tickets appear here once issued.</p>
                                </div>
                            </div>
                        @endif
                    </section>
                </div>

                <section class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-rose-50/60 p-4 sm:p-5" aria-labelledby="recently-called-heading">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h2 id="recently-called-heading" class="text-base font-semibold text-amber-900">Recently Called</h2>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ $recentlyCalled->count() }} ticket(s)</span>
                            <span class="text-xs text-slate-500">Not served after 1 minute</span>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <div class="grid grid-cols-[1fr_auto] gap-2 border-b border-slate-200 bg-slate-50 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                            <span>Ticket / Status</span>
                            <span>Time</span>
                        </div>

                        <div class="max-h-[340px] overflow-y-auto p-2">
                            <div class="space-y-2">
                                @forelse($recentlyCalled as $entry)
                                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-3.5 py-3">
                                        <div>
                                            <p class="text-lg font-semibold leading-tight text-slate-800">{{ $entry->queue_number }}</p>
                                            <p class="mt-0.5 text-xs font-semibold tracking-wide text-rose-600">NOT SERVED</p>
                                        </div>
                                        <span class="text-xs font-medium text-slate-500">{{ $entry->served_at?->format('h:i:s A') }}</span>
                                    </div>
                                @empty
                                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-12 text-center">
                                        <p class="text-sm text-slate-500">No recently called ticket yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </div>
</div>
