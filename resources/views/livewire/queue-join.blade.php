<div class="min-h-screen flex flex-col items-center justify-center p-4 bg-slate-50">
    @if(!$joined)
        <div class="lgu-card rounded-2xl max-w-md w-full overflow-hidden border-2 border-slate-200">
            <div class="bg-blue-800 px-6 py-5 text-center">
                <h1 class="text-xl font-bold text-white">{{ $office->name }}</h1>
                <p class="text-blue-200 text-sm mt-0.5">{{ $office->description }}</p>
            </div>
            <div class="p-8">
                <p class="text-slate-600 mb-6">Scan this QR code to get your queue number for this office, or tap the button below to join the queue now.</p>
                <button wire:click="joinQueue" type="button"
                        class="lgu-btn w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-4 rounded-xl text-lg transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Get my queue number
                </button>
            </div>
        </div>
    @else
        <div class="lgu-card rounded-2xl max-w-md w-full overflow-hidden text-center border-2 border-slate-200">
            <div class="bg-blue-800 px-6 py-5">
                <h1 class="text-xl font-bold text-white">{{ $office->name }}</h1>
            </div>
            <div class="p-8">
                <p class="text-slate-600 mb-4">Your queue number is</p>
                <p class="text-5xl font-bold text-emerald-600 mb-2" aria-label="Queue number {{ $entry->queue_number }}">{{ $entry->queue_number }}</p>
                <p class="text-slate-500 text-sm">Please wait for your number to be called.</p>
                <p class="text-slate-400 text-xs mt-4">You may close this page. Your number is saved.</p>
            </div>
        </div>
    @endif
</div>
