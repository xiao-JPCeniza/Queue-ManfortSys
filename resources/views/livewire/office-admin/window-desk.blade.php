<div wire:poll.2s class="space-y-6">
    @if(session('office_message'))
        <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
            {{ session('office_message') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_60px_-42px_rgba(15,23,42,0.45)]">
        <div class="bg-[linear-gradient(135deg,#0f2d52_0%,#174a7a_62%,#1f6aa5_100%)] px-6 py-6 text-white sm:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-blue-100">Window Control Tab</p>
                    <h1 class="mt-2 text-3xl font-semibold">{{ $office->name }} {{ $windowLabel }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-blue-100/95">
                        Focused service window screen for calling the next client and completing the current transaction.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('office.dashboard', $office->slug) }}"
                        class="inline-flex items-center rounded-full border border-white/35 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
                    >
                        Back to Operations Desk
                    </a>

                    <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] {{ $windowEntry ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-slate-100 text-slate-600' }}">
                        {{ $windowEntry ? 'Serving' : 'Idle' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.8fr)] lg:p-8">
            <section class="rounded-[1.75rem] border border-slate-200 bg-[linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.45)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">{{ $windowLabel }}</p>
                        <h2 class="mt-2 text-3xl font-semibold text-slate-900">{{ $windowEntry?->queue_number ?? 'Available' }}</h2>
                    </div>

                    <span class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] {{ $windowEntry ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-slate-100 text-slate-600' }}">
                        {{ $windowEntry ? 'Serving' : 'Idle' }}
                    </span>
                </div>

                @if($windowEntry)
                    <div class="mt-6 rounded-[1.5rem] border border-emerald-200 bg-[linear-gradient(180deg,#f3fff9_0%,#e9f8f0_100%)] p-6">
                        <p class="text-sm font-medium text-slate-500">Ticket Number</p>
                        <p class="mt-2 text-5xl font-semibold tracking-[0.06em] text-slate-900">{{ $windowEntry->queue_number }}</p>
                        <p class="mt-4 inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] {{ $windowEntry->isPriorityClient() ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $windowEntry->client_type_label }}
                        </p>
                        <p class="mt-4 text-sm text-slate-500">Called at {{ $windowEntry->displayCalledAt()?->format('h:i A') }}</p>
                    </div>
                @else
                    <div class="mt-6 flex min-h-[18rem] items-center justify-center rounded-[1.5rem] border border-dashed border-sky-200 bg-slate-50 px-6 text-center">
                        <p class="max-w-sm text-lg text-slate-500">{{ $windowLabel }} is ready for the next client.</p>
                    </div>
                @endif

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <button
                        type="button"
                        wire:click="callNext"
                        wire:loading.attr="disabled"
                        wire:target="callNext"
                        class="inline-flex items-center justify-center rounded-2xl bg-amber-400 px-5 py-4 text-base font-semibold text-slate-900 shadow-[0_18px_28px_-22px_rgba(245,158,11,0.95)] transition hover:bg-amber-300 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Call Next
                    </button>

                    @if($windowEntry)
                        <button
                            type="button"
                            wire:click="complete({{ $windowEntry->id }})"
                            class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-4 text-base font-semibold text-white shadow-[0_18px_28px_-22px_rgba(5,150,105,0.95)] transition hover:bg-emerald-500"
                        >
                            Mark Completed
                        </button>
                    @else
                        <div class="hidden rounded-2xl border border-dashed border-slate-200 sm:block"></div>
                    @endif
                </div>
            </section>

            <aside class="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-[0_18px_40px_-34px_rgba(15,23,42,0.4)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Waiting Line</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $waiting->count() }} waiting</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-600">
                        {{ $office->prefix }}
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($waiting as $entry)
                        <div class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-base font-semibold text-slate-900">{{ $entry->queue_number }}</p>
                                <p class="mt-1 text-xs font-medium uppercase tracking-[0.12em] {{ $entry->isPriorityClient() ? 'text-amber-700' : 'text-slate-500' }}">
                                    {{ $entry->client_type_label }}
                                </p>
                                <p class="mt-2 text-xs text-slate-500">Joined {{ $entry->displayCreatedAt()?->format('h:i A') }}</p>
                            </div>

                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500">#{{ $loop->iteration }}</span>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                            No one waiting in queue.
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </section>
</div>
