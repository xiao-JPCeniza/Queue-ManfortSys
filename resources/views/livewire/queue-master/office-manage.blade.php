<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('queue-master.index') }}" class="text-emerald-600 hover:underline text-sm">← Queue Master</a>
            <h1 class="text-2xl font-bold text-slate-800 mt-1">{{ $office->name }}</h1>
            <p class="text-slate-500">{{ $office->description }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="font-semibold text-slate-800 mb-4">QR Code for this office</h2>
            <p class="text-sm text-slate-600 mb-4">Print or display this QR code. Clients scan it to get a queue number for <strong>{{ $office->name }}</strong>.</p>
            <div class="inline-block p-4 bg-white border border-slate-200 rounded-lg">
                {!! $qrSvg !!}
            </div>
            <p class="text-xs text-slate-500 mt-4">Join URL: {{ $office->getQueueJoinUrl() }}</p>
            <div class="mt-4">
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 text-sm">
                    Print QR Code
                </button>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="font-semibold text-slate-800 mb-4">Office queue</h2>
            <p class="text-sm text-slate-600 mb-4">Manage this office's queue or open as Office Admin view.</p>
            <a href="{{ route('office.dashboard', $office->slug) }}" class="inline-block px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
                Open office queue dashboard
            </a>
        </div>
    </div>
</div>
