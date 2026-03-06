<div>
    <div class="mb-6">
        @php($mainDashboardRoute = auth()->user()?->isSuperAdmin() ? route('super-admin.index') : route('queue-master.index'))
        <a href="{{ $mainDashboardRoute }}" class="lgu-btn inline-flex items-center gap-1 text-blue-800 font-medium text-sm hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-1">
            &larr; Dashboard
        </a>
        <h1 class="lgu-page-title mt-1">{{ $office->name }}</h1>
        <p class="text-slate-600 text-sm mt-0.5">{{ $office->description }}</p>
    </div>

    <section class="lgu-card p-6 mb-6" aria-labelledby="manage-guide-heading">
        <h2 id="manage-guide-heading" class="lgu-section-title mb-3">How to use this page</h2>
        <ol class="space-y-2 text-sm text-slate-700 list-decimal list-inside">
            <li>Print or display the QR code so clients can get a number for <strong>{{ $office->name }}</strong>.</li>
            <li>Open the office queue dashboard to start serving people in line.</li>
            <li>Use <strong>Call next number</strong> to call the next waiting client.</li>
            <li>Use <strong>Mark completed</strong> after the transaction is finished.</li>
            <li>Reset numbering from Dashboard only when starting a new queue cycle.</li>
        </ol>
    </section>

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
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('office.dashboard', $office->slug) }}" class="lgu-btn inline-flex px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Open office queue dashboard
                </a>
                @if($office->slug === 'hrmo')
                    <a href="{{ route('office.hrmo.monitor', $office->slug) }}" class="lgu-btn inline-flex px-5 py-2.5 bg-blue-700 text-white rounded-xl hover:bg-blue-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Open HRMO live monitor
                    </a>
                @elseif(in_array($office->slug, ['business-permits', 'bplo'], true))
                    <a href="{{ route('office.bplo.monitor', $office->slug) }}" class="lgu-btn inline-flex px-5 py-2.5 bg-blue-700 text-white rounded-xl hover:bg-blue-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Open BPLO live monitor
                    </a>
                @else
                    <a href="{{ route('office.hrmo.monitor', $office->slug) }}" class="lgu-btn inline-flex px-5 py-2.5 bg-blue-700 text-white rounded-xl hover:bg-blue-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Open live monitor
                    </a>
                @endif
            </div>
        </section>
    </div>
</div>
