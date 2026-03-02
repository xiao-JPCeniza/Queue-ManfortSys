<div>
    @if(session('office_message'))
        <div class="mb-4 p-3 bg-emerald-100 border border-emerald-400 text-emerald-800 rounded-lg text-sm">{{ session('office_message') }}</div>
    @endif
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ $office->name }}</h1>
        <p class="text-slate-500">Office queue dashboard</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800">Currently serving</h2>
                <button wire:click="callNext" type="button"
                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium text-sm">
                    Call next number
                </button>
            </div>
            <div class="p-6">
                @if($serving)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-4xl font-bold text-emerald-600">{{ $serving->queue_number }}</p>
                            <p class="text-slate-500 text-sm">Called at {{ $serving->called_at?->format('h:i A') }}</p>
                        </div>
                        <button wire:click="complete({{ $serving->id }})" type="button"
                                class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 text-sm">
                            Mark completed
                        </button>
                    </div>
                @else
                    <p class="text-slate-500 text-center py-4">No one is being served. Click "Call next number" to start.</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <h2 class="px-4 py-3 font-semibold text-slate-800 border-b border-slate-200">Waiting ({{ $waiting->count() }})</h2>
            <div class="max-h-80 overflow-y-auto">
                @forelse($waiting as $entry)
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <span class="font-medium">{{ $entry->queue_number }}</span>
                        <span class="text-xs text-slate-400">{{ $entry->created_at->format('h:i A') }}</span>
                    </div>
                @empty
                    <p class="px-4 py-6 text-slate-500 text-center text-sm">No one waiting.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('queue.join', $office->slug) }}" target="_blank" class="text-emerald-600 hover:underline text-sm">
            Open queue join page (for clients) →
        </a>
    </div>
</div>
