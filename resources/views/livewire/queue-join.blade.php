<div class="min-h-screen flex flex-col items-center justify-center p-4 bg-slate-50">
    @if(!$joined)
        <div class="lgu-card rounded-2xl max-w-md w-full overflow-hidden border-2 border-slate-200">
            <div class="bg-blue-800 px-6 py-5 text-center">
                <h1 class="text-xl font-bold text-white">{{ $office->name }}</h1>
                <p class="text-blue-200 text-sm mt-0.5">{{ $office->description }}</p>
            </div>
            <div class="p-8">
                <p class="text-slate-600 mb-6">Select the ticket type you need for this office. Your queue number will be generated right away after you choose.</p>
                <div class="grid gap-3">
                    <button
                        wire:click="joinQueue('{{ \App\Models\QueueEntry::TYPE_REGULAR }}')"
                        type="button"
                        class="lgu-btn w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-4 rounded-xl text-lg transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    >
                        Regular
                    </button>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.14em] text-amber-700">Priority</p>
                        <div class="grid gap-2">
                            <button
                                wire:click="joinQueue('{{ \App\Models\QueueEntry::TYPE_PWD }}')"
                                type="button"
                                class="lgu-btn w-full bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold py-3 rounded-xl text-base transition focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                            >
                                PWD
                            </button>
                            <button
                                wire:click="joinQueue('{{ \App\Models\QueueEntry::TYPE_SENIOR_CITIZEN }}')"
                                type="button"
                                class="lgu-btn w-full bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold py-3 rounded-xl text-base transition focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                            >
                                Senior Citizen
                            </button>
                            <button
                                wire:click="joinQueue('{{ \App\Models\QueueEntry::TYPE_PREGNANT }}')"
                                type="button"
                                class="lgu-btn w-full bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold py-3 rounded-xl text-base transition focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                            >
                                Pregnant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="lgu-card rounded-2xl max-w-md w-full overflow-hidden text-center border-2 border-slate-200">
            <div class="bg-blue-800 px-6 py-5">
                <h1 class="text-xl font-bold text-white">{{ $office->name }}</h1>
            </div>
            <div class="p-8">
                <p class="inline-flex items-center justify-center rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">{{ $entry->client_type_label }}</p>
                <p class="text-slate-600 mb-4">Your queue number is</p>
                <p class="text-5xl font-bold text-emerald-600 mb-2" aria-label="Queue number {{ $entry->queue_number }}">{{ $entry->queue_number }}</p>
                <p class="text-slate-500 text-sm">Please wait for your number to be called.</p>
                <p class="text-slate-400 text-xs mt-4">You may close this page. Your number is saved.</p>
            </div>
        </div>
    @endif
</div>
