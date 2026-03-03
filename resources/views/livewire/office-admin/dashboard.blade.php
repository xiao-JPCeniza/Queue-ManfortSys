<div wire:poll.5s>
    @if(session('office_message'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl text-sm" role="status">{{ session('office_message') }}</div>
    @endif
    <div class="mb-6">
        <h1 class="lgu-page-title">{{ $office->name }}</h1>
        <p class="text-slate-600 text-sm mt-1">Office queue dashboard — call numbers and manage the line.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 lgu-card overflow-hidden" aria-labelledby="serving-heading">
            <div class="px-5 py-4 bg-blue-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                <h2 id="serving-heading" class="lgu-section-title text-slate-800">Currently Serving</h2>
                <div class="flex items-center gap-2">
                    <button wire:click="callNext" type="button"
                            class="lgu-btn px-5 py-3 bg-emerald-600 text-white rounded-xl font-semibold text-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 shadow-sm">
                        Call next number
                    </button>
                    <button type="button"
                            onclick="window.callServingNumber(@js($serving?->queue_number), @js($office->name))"
                            @disabled(!$serving)
                            class="lgu-btn inline-flex items-center gap-2 px-4 py-3 bg-amber-500 text-slate-900 rounded-xl font-semibold text-sm hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5 6 9H3v6h3l5 4V5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a5 5 0 0 1 0 7" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 6a9 9 0 0 1 0 12" />
                        </svg>
                        Call
                    </button>
                </div>
            </div>
            <div class="p-6 min-h-[140px] flex flex-col justify-center">
                @if($serving)
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-4xl sm:text-5xl font-bold text-emerald-600 tracking-tight" aria-label="Current queue number {{ $serving->queue_number }}">{{ $serving->queue_number }}</p>
                            <p class="text-slate-500 text-sm mt-1">Called at {{ $serving->called_at?->format('h:i A') }}</p>
                        </div>
                        <button wire:click="complete({{ $serving->id }})" type="button"
                                class="lgu-btn px-5 py-3 bg-slate-700 text-white rounded-xl font-medium text-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                            Mark completed
                        </button>
                    </div>
                @else
                    <p class="text-slate-500 text-center py-4">No one served right now.</p>
                @endif
            </div>
        </section>

        <section class="lgu-card overflow-hidden" aria-labelledby="waiting-heading">
            <h2 id="waiting-heading" class="px-5 py-4 lgu-section-title border-b border-slate-200 bg-slate-50">
                Waiting <span class="font-normal text-slate-500">({{ $waiting->count() }})</span>
            </h2>
            <div class="max-h-80 overflow-y-auto">
                @forelse($waiting as $entry)
                    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between hover:bg-slate-50/50">
                        <span class="font-semibold text-slate-800">{{ $entry->queue_number }}</span>
                        <span class="text-xs text-slate-400">{{ $entry->created_at->format('h:i A') }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-slate-500 text-center text-sm">No one waiting.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('queue.join', $office->slug) }}" target="_blank" rel="noopener noreferrer"
           class="lgu-btn inline-flex items-center gap-2 px-4 py-2.5 text-blue-800 bg-blue-50 rounded-xl font-medium text-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Open queue join page (for clients)
            <span aria-hidden="true">&rarr;</span>
        </a>

        @if($office->slug === 'hrmo')
            <a href="{{ route('office.hrmo.monitor', $office->slug) }}"
               class="lgu-btn inline-flex items-center gap-2 px-4 py-2.5 text-white bg-emerald-600 rounded-xl font-medium text-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Open HRMO live monitor
            </a>
        @endif
    </div>
</div>

@script
<script>
    window.callServingNumber = (queueNumber, officeName) => {
        if (!queueNumber || !('speechSynthesis' in window)) {
            return;
        }

        window.speechSynthesis.cancel();

        const toSpokenQueue = (value) => {
            const [prefix, number] = value.split('-');

            if (!number) {
                return value.split('').join(' ');
            }

            const letters = prefix.split('').join(' ');
            return `${letters} ${number}`;
        };

        const spokenQueue = toSpokenQueue(queueNumber);
        const message = `Now serving ${spokenQueue} at ${officeName}. Please proceed to the office.`;
        const announcement = new SpeechSynthesisUtterance(message);
        announcement.lang = 'en-US';
        announcement.rate = 0.95;

        const voices = window.speechSynthesis.getVoices();
        const englishVoice = voices.find((voice) => voice.lang.startsWith('en-US'))
            || voices.find((voice) => voice.lang.startsWith('en-'));

        if (englishVoice) {
            announcement.voice = englishVoice;
        }

        window.speechSynthesis.speak(announcement);
    };

    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
    }
</script>
@endscript
