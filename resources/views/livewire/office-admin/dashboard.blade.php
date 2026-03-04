<div wire:poll.5s>
    @if(session('office_message'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl text-sm" role="status">{{ session('office_message') }}</div>
    @endif

    @if($office->slug !== 'hrmo')
        <div class="mb-6">
            <h1 class="lgu-page-title">{{ $office->name }}</h1>
            <p class="text-slate-600 text-sm mt-1">Office queue dashboard - call numbers and manage the line.</p>
        </div>
    @endif

    @if($office->slug === 'hrmo')
        <div class="overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm">
            <div class="min-w-0 bg-white">
                    <div class="p-4 sm:p-6">
                        @if($hrmoTab === 'dashboard')
                            @include('livewire.office-admin.partials.queue-dashboard-panel', ['showHrmoMonitor' => true])
                        @endif

                        @if($hrmoTab === 'reports' && $summary)
                            <section class="lgu-card p-6" aria-labelledby="summary-heading">
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
                        @endif

                        @if($hrmoTab === 'queue-management')
                            <section class="lgu-card p-6" aria-labelledby="overall-activity-heading">
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
                                                            {{ $entry->status === 'serving' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                            {{ $entry->status === 'waiting' ? 'bg-amber-100 text-amber-700' : '' }}
                                                            {{ $entry->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                            {{ $entry->status === 'not_served' ? 'bg-red-100 text-red-700' : '' }}">
                                                            {{ strtoupper(str_replace('_', ' ', $entry->status)) }}
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
                        @endif
                    </div>
            </div>
        </div>
    @else
        @include('livewire.office-admin.partials.queue-dashboard-panel')
    @endif
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
