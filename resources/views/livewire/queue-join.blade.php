<div class="min-h-screen flex flex-col items-center justify-center p-4">
    @if(!$joined)
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="bg-emerald-800 px-6 py-4 text-center">
                <h1 class="text-xl font-bold text-white">{{ $office->name }}</h1>
                <p class="text-emerald-200 text-sm">{{ $office->description }}</p>
            </div>
            <div class="p-8">
                <p class="text-slate-600 mb-6">Scan this QR code to get your queue number for this office. Or tap the button below to join the queue now.</p>
                <button wire:click="joinQueue" type="button"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-4 rounded-xl text-lg transition">
                    Get my queue number
                </button>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden text-center">
            <div class="bg-emerald-800 px-6 py-4">
                <h1 class="text-xl font-bold text-white">{{ $office->name }}</h1>
            </div>
            <div class="p-8">
                <p class="text-slate-600 mb-4">Your queue number is</p>
                <p class="text-5xl font-bold text-emerald-600 mb-2">{{ $entry->queue_number }}</p>
                <p class="text-slate-500 text-sm">Please wait for your number to be called.</p>
                <p class="text-slate-400 text-xs mt-4">You may close this page. Your number is saved.</p>
            </div>
        </div>
    @endif
</div>
