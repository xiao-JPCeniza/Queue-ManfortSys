<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="lgu-page-title mb-1">Offices</h1>
            <p class="text-sm text-slate-600">Manage the offices that appear on the public queue page.</p>
        </div>

        <button
            type="button"
            wire:click="toggleCreateForm"
            class="lgu-btn inline-flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            + Add Office
        </button>
    </div>

    @if($showCreateForm)
        <section class="lgu-card p-6" aria-labelledby="add-office-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="add-office-heading" class="lgu-section-title">Add Office</h2>
                    <p class="mt-1 text-sm text-slate-500">New offices are added to the public queue immediately after saving.</p>
                </div>
                <button
                    type="button"
                    wire:click="toggleCreateForm"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Cancel
                </button>
            </div>

            <form wire:submit="createOffice" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="office-name" class="mb-2 block text-sm font-medium text-slate-700">Office Name</label>
                    <input
                        id="office-name"
                        type="text"
                        wire:model="officeName"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="e.g. Business Center"
                    >
                    @error('officeName')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="office-prefix" class="mb-2 block text-sm font-medium text-slate-700">Prefix Ticket</label>
                    <input
                        id="office-prefix"
                        type="text"
                        wire:model="officePrefix"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm uppercase text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="e.g. BCEN"
                    >
                    @error('officePrefix')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="office-description" class="mb-2 block text-sm font-medium text-slate-700">Meaning or Description of the Office</label>
                    <textarea
                        id="office-description"
                        wire:model="officeDescription"
                        rows="3"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="e.g. Business permits and licensing services"
                    ></textarea>
                    @error('officeDescription')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <button
                        type="submit"
                        class="lgu-btn rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    >
                        Save Office
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="lgu-card overflow-hidden" aria-labelledby="public-office-list-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-4">
            <div>
                <h2 id="public-office-list-heading" class="lgu-section-title">Public Queue Offices</h2>
                <p class="mt-1 text-sm text-slate-500">These offices are currently visible on <span class="font-medium text-slate-700">Municipality Queue Services</span>.</p>
            </div>
            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                {{ $offices->count() }} office{{ $offices->count() === 1 ? '' : 's' }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-slate-500">
                        <th class="px-6 py-3 font-semibold">Office Name</th>
                        <th class="px-6 py-3 font-semibold">Label</th>
                        <th class="px-6 py-3 font-semibold">Prefix Ticket</th>
                        <th class="px-6 py-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offices as $office)
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $office->name }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $office->display_name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $office->prefix }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a
                                        href="{{ route('queue.join', $office->slug) }}"
                                        class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Queue Link
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="deleteOffice({{ $office->id }})"
                                        wire:confirm="Delete {{ $office->display_name }}? This will remove the office, delete its queue entries, and unassign any linked users."
                                        class="inline-flex items-center rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No public queue offices are configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
