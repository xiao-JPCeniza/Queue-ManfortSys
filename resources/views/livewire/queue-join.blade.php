<div class="min-h-screen flex flex-col items-center justify-center p-4 bg-slate-50">
    @php($isMtoOffice = in_array($office->slug, ['treasury', 'mto'], true))
    @php($isCivilRegistryOffice = $office->slug === 'civil-registry')
    @if(!$joined)
        <div class="lgu-card rounded-2xl {{ $queueServiceOptions !== [] && !$selectedQueueService ? 'max-w-5xl' : 'max-w-md' }} w-full overflow-hidden border-2 border-slate-200">
            <div class="bg-blue-800 px-6 py-5 text-center">
                <h1 class="text-xl font-bold text-white">{{ $office->name }}</h1>
                <p class="text-blue-200 text-sm mt-0.5">{{ $office->display_description }}</p>
            </div>
            <div class="p-8">
                @if($queueServiceOptions !== [] && !$selectedQueueService)
                    <p class="text-slate-600 mb-6">
                        {{ $isMtoOffice
                            ? 'Select the MTO service you need first. After that, you can choose whether your ticket is Regular or Priority.'
                            : ($isCivilRegistryOffice
                                ? 'Select the window you need first. The services handled by each window are shown below. After that, you can choose whether your ticket is Regular or Priority.'
                                : 'Select the service you need first. After that, you can choose whether your ticket is Regular or Priority.') }}
                    </p>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($queueServiceOptions as $serviceKey => $serviceOption)
                            <button
                                wire:click="selectService('{{ $serviceKey }}')"
                                type="button"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-4 text-left shadow-sm transition hover:border-blue-300 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <span class="block text-base font-semibold text-slate-900">{{ $serviceOption['label'] }}</span>
                                <span class="mt-1 block text-sm text-slate-500">{{ $serviceOption['description'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-600 mb-6">
                        Select the ticket type you need for this office. Your queue number will be generated right away after you choose.
                    </p>

                    @if($selectedQueueService)
                        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">Selected Service</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $selectedQueueService['label'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $selectedQueueService['description'] }}</p>
                        </div>
                    @endif

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
                                @foreach($priorityClientTypeOptions as $clientType => $option)
                                    <button
                                        wire:click="joinQueue('{{ $clientType }}')"
                                        type="button"
                                        class="lgu-btn w-full bg-amber-500 hover:bg-amber-600 text-slate-900 font-semibold py-3 rounded-xl text-base transition focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
                                    >
                                        {{ $option['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if($queueServiceOptions !== [] && $selectedQueueService)
                            <button
                                wire:click="resetServiceSelection"
                                type="button"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                            >
                                Choose Another Service
                            </button>
                        @endif
                    </div>
                @endif
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
