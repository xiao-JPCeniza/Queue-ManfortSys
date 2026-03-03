<div>
    <div class="mb-6">
        <a href="{{ route('queue-master.index') }}" class="lgu-btn inline-flex items-center gap-1 text-blue-800 font-medium text-sm hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-1">
            ← Queue Master
        </a>
        <h1 class="lgu-page-title mt-1">{{ $office->name }}</h1>
        <p class="text-slate-600 text-sm mt-0.5">{{ $office->description }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="lgu-card p-6" aria-labelledby="qr-heading">
            <h2 id="qr-heading" class="lgu-section-title mb-4">QR Code for this office</h2>
            <p class="text-sm text-slate-600 mb-4">Print or display this QR code. Clients scan it to get a queue number for <strong>{{ $office->name }}</strong>.</p>
            <div class="inline-block p-4 bg-white border border-slate-200 rounded-xl">
                {!! $qrSvg !!}
            </div>
            <p class="text-xs text-slate-500 mt-4 break-all">Join URL: {{ $office->getQueueJoinUrl() }}</p>
            <div class="mt-4">
                <button type="button" onclick="window.print()" class="lgu-btn px-4 py-2.5 bg-slate-700 text-white rounded-xl hover:bg-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Print QR Code
                </button>
            </div>
        </section>
        <section class="lgu-card p-6" aria-labelledby="queue-heading">
            <h2 id="queue-heading" class="lgu-section-title mb-4">Office queue</h2>
            <p class="text-sm text-slate-600 mb-4">Manage this office's queue or open as Office Admin view.</p>
            <a href="{{ route('office.dashboard', $office->slug) }}" class="lgu-btn inline-flex px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Open office queue dashboard
            </a>
        </section>
    </div>
</div>
