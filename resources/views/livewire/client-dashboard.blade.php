<div class="min-h-screen flex flex-col bg-slate-50" x-data="clientQueue()">
    {{-- Header: LGU theme --}}
    <header class="bg-blue-800 text-white shadow-md">
        <div class="max-w-5xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">Get Your Queue Number</h1>
                <p class="text-blue-200 text-sm">Municipality of Manolo Fortich — Select an office</p>
            </div>
            <a href="{{ route('home') }}" class="lgu-btn px-4 py-2.5 bg-white/20 hover:bg-white/30 rounded-xl font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">
                Home
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-5xl mx-auto w-full p-6" role="main">
        @if(!$ticket)
            {{-- Office selection --}}
            <p class="text-slate-600 text-center mb-8 max-w-xl mx-auto">Select the office you want to visit. Your ticket number will be generated and announced.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" role="list">
                @foreach($offices as $office)
                    <button
                        type="button"
                        wire:click="selectOffice({{ $office->id }})"
                        wire:loading.attr="disabled"
                        class="lgu-card group relative rounded-2xl border-2 border-slate-200 hover:border-blue-500 hover:shadow-lg p-6 text-left transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 disabled:pointer-events-none min-h-[120px]"
                        role="listitem"
                    >
                        <span class="text-xl font-bold text-slate-800 group-hover:text-blue-700 block">{{ $office->name }}</span>
                        <p class="text-slate-500 text-sm mt-1">{{ $office->description }}</p>
                        <span class="absolute top-4 right-4 text-slate-300 group-hover:text-blue-500 transition" aria-hidden="true">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                        <span wire:loading wire:target="selectOffice({{ $office->id }})" class="absolute inset-0 flex items-center justify-center bg-white/90 rounded-2xl">
                            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </span>
                    </button>
                @endforeach
            </div>
        @else
            {{-- Ticket result --}}
            <div class="max-w-md mx-auto">
                <div class="lgu-card rounded-2xl overflow-hidden text-center border-2 border-slate-200">
                    <div class="bg-blue-800 px-6 py-5">
                        <h2 class="text-xl font-bold text-white">{{ $ticket['office_name'] }}</h2>
                        <p class="text-blue-200 text-sm mt-0.5">Your queue number</p>
                    </div>
                    <div class="p-8">
                        <p class="text-5xl sm:text-6xl font-bold text-emerald-600 tracking-tight" id="ticket-number-display" aria-label="Your queue number is {{ $ticket['queue_number'] }}">{{ $ticket['queue_number'] }}</p>
                        <p class="text-slate-500 text-sm mt-4">Please wait for your number to be called at the office.</p>
                        <button
                            type="button"
                            wire:click="clearTicket"
                            class="lgu-btn mt-6 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                        >
                            Get another ticket
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </main>

    {{-- Pop-up notification (modal) --}}
    <div x-show="showPopup" x-cloak x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @ticket-issued.window="onTicketIssued($event.detail)"
         @click.self="showPopup = false"
         role="dialog"
         aria-modal="true"
         aria-labelledby="popup-title"
    >
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center border-2 border-emerald-500" @click.stop>
            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-4" aria-hidden="true">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 id="popup-title" class="text-lg font-bold text-slate-800">Ticket issued</h3>
            <p class="text-3xl font-bold text-emerald-600 mt-2" x-text="popupNumber"></p>
            <p class="text-slate-500 text-sm mt-1" x-text="popupOffice"></p>
            <button type="button" @click="showPopup = false" class="lgu-btn mt-4 w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                OK
            </button>
        </div>
    </div>

    <footer class="py-4 text-center text-slate-500 text-sm border-t border-slate-200 bg-white">
        Municipality of Manolo Fortich &copy; {{ date('Y') }} — Queue Management System
    </footer>
</div>

@script
<script>
    function clientQueue() {
        return {
            showPopup: false,
            popupNumber: '',
            popupOffice: '',
            speechSynth: null,

            init() {
                this.speechSynth = window.speechSynthesis;
            },

            onTicketIssued(detail) {
                const queueNumber = detail.queueNumber || '';
                const officeName = detail.officeName || '';
                this.popupNumber = queueNumber;
                this.popupOffice = officeName;
                this.showPopup = true;
                this.speakTicket(queueNumber, officeName);
                this.maybeBrowserNotify(queueNumber, officeName);
            },

            speakTicket(queueNumber, officeName) {
                if (!this.speechSynth) return;
                const parts = queueNumber.split('-');
                const prefix = parts[0] || '';
                const num = parts[1] || '';
                const prefixLetters = prefix.split('').join(' ');
                const text = `Your queue number for ${officeName} is ${prefixLetters} ${num}. Please wait for your number to be called.`;
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'en-PH';
                u.rate = 0.9;
                this.speechSynth.speak(u);
            },

            maybeBrowserNotify(queueNumber, officeName) {
                if (!('Notification' in window)) return;
                if (Notification.permission === 'granted') {
                    new Notification('Ticket issued — ' + officeName, {
                        body: 'Your queue number is ' + queueNumber,
                        icon: '/favicon.ico'
                    });
                } else if (Notification.permission !== 'denied') {
                    Notification.requestPermission().then(p => {
                        if (p === 'granted')
                            new Notification('Ticket issued — ' + officeName, { body: 'Your queue number is ' + queueNumber, icon: '/favicon.ico' });
                    });
                }
            }
        };
    }
</script>
@endscript

<style>
    [x-cloak] { display: none !important; }
</style>
