<div>
    <div class="mb-8">
        <h1 class="lgu-page-title mb-1">Queueing Offices</h1>
        <p class="text-slate-600 text-sm">Add and remove queueing offices. Each new office gets an admin account with generated login credentials (shown once).</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm" role="alert">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mb-8" aria-labelledby="add-office-heading">
        <h2 id="add-office-heading" class="lgu-section-title mb-4">Add office</h2>
        <div class="lgu-card p-6">
            <form wire:submit="addOffice" class="space-y-4 max-w-xl">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Office name <span class="text-red-600">*</span></label>
                    <input type="text" id="name" wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                           placeholder="e.g. MISO" required autocomplete="off">
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium text-slate-700 mb-1">Slug (URL-friendly, unique) <span class="text-red-600">*</span></label>
                    <input type="text" id="slug" wire:model="slug" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                           placeholder="e.g. miso" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" autocomplete="off">
                    <p class="text-xs text-slate-500 mt-1">Lowercase letters, numbers, and hyphens only. Used for queue URL and login username.</p>
                </div>
                <div>
                    <label for="prefix" class="block text-sm font-medium text-slate-700 mb-1">Queue number prefix (optional)</label>
                    <input type="text" id="prefix" wire:model="prefix" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                           placeholder="e.g. MISO" maxlength="20" autocomplete="off">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
                    <textarea id="description" wire:model="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-slate-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                              placeholder="Brief description"></textarea>
                </div>
                <button type="submit" class="lgu-btn px-5 py-2.5 bg-blue-800 text-white rounded-xl hover:bg-blue-700 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add office & generate credentials
                </button>
            </form>
        </div>
    </section>

    <section class="mb-8" aria-labelledby="offices-list-heading">
        <h2 id="offices-list-heading" class="lgu-section-title mb-4">Offices</h2>
        <div class="lgu-card overflow-hidden">
            @if ($offices->isEmpty())
                <p class="p-6 text-slate-500 text-sm">No offices yet. Add one above.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" role="table" aria-label="Queueing offices">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Name</th>
                                <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Slug</th>
                                <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Prefix</th>
                                <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Admin accounts</th>
                                <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($offices as $office)
                                <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $office->name }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $office->slug }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $office->prefix ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $office->users_count }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('queue-master.office', $office->slug) }}" class="text-blue-700 hover:underline font-medium mr-3">Manage</a>
                                        <button type="button"
                                                wire:click="removeOffice({{ $office->id }})"
                                                wire:confirm="Remove office \"{{ $office->name }}\"? Associated admin account(s) will be deleted. This cannot be undone."
                                                class="text-red-700 hover:underline font-medium">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <section aria-labelledby="audit-heading">
        <h2 id="audit-heading" class="lgu-section-title mb-4">Recent audit log</h2>
        <div class="lgu-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" role="table" aria-label="Audit log">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Time</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Actor</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Action</th>
                            <th scope="col" class="text-left px-4 py-3 font-semibold text-slate-700">Subject</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($auditLogs as $log)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3 text-slate-600">{{ $log->created_at->format('M j, Y H:i') }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">{{ $log->action }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ class_basename($log->subject_type) }}@if($log->subject_id) #{{ $log->subject_id }}@endif</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">No audit entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    @if ($showCredentials)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" role="dialog" aria-modal="true" aria-labelledby="credentials-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-200">
                <h3 id="credentials-title" class="text-lg font-semibold text-slate-900 mb-2">Credentials for {{ $showCredentials['office_name'] }}</h3>
                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                    Copy these now. They will not be shown again. The office admin can change the password after first login.
                </p>
                <dl class="space-y-2 mb-6">
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Username (email)</dt>
                        <dd class="mt-0.5 flex items-center gap-2">
                            <code id="cred-username" class="flex-1 rounded bg-slate-100 px-2 py-1.5 text-sm font-mono break-all">{{ $showCredentials['username'] }}</code>
                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('cred-username').textContent); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy', 2000)" class="lgu-btn px-2 py-1 text-xs bg-slate-200 rounded hover:bg-slate-300">Copy</button>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Password</dt>
                        <dd class="mt-0.5 flex items-center gap-2">
                            <code id="cred-password" class="flex-1 rounded bg-slate-100 px-2 py-1.5 text-sm font-mono break-all">{{ $showCredentials['password'] }}</code>
                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('cred-password').textContent); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy', 2000)" class="lgu-btn px-2 py-1 text-xs bg-slate-200 rounded hover:bg-slate-300">Copy</button>
                        </dd>
                    </div>
                </dl>
                <button type="button" wire:click="dismissCredentials" class="lgu-btn w-full px-4 py-2.5 bg-blue-800 text-white rounded-xl hover:bg-blue-700 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    I have copied these credentials
                </button>
            </div>
        </div>
    @endif
</div>
