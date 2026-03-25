<div class="space-y-6">
    @php($generatedOfficeAccount = session('generatedOfficeAccount'))

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(is_array($generatedOfficeAccount))
        <section class="rounded-2xl border border-blue-200 bg-blue-50/70 p-5 shadow-sm" aria-labelledby="generated-office-account-heading">
            <div>
                <div>
                    <h2 id="generated-office-account-heading" class="text-base font-semibold text-blue-950">
                        {{ $generatedOfficeAccount['office_name'] }} account is ready
                    </h2>
                    <p class="mt-1 text-sm text-blue-800">
                        Use these credentials to sign in to the new office operation desk.
                    </p>
                </div>
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-blue-100 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-blue-700">Login Email</p>
                    <p class="mt-1 break-all font-mono text-sm text-slate-800">{{ $generatedOfficeAccount['email'] }}</p>
                </div>

                <div class="rounded-xl border border-blue-100 bg-white px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-blue-700">Password</p>
                    <p class="mt-1 break-all font-mono text-sm text-slate-800">{{ $generatedOfficeAccount['password'] }}</p>
                </div>
            </div>
        </section>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

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

    <section
        class="lgu-card p-6"
        aria-labelledby="service-window-setup-heading"
        x-data="serviceWindowSetup({
            initialOfficeSlug: @js($serviceWindowOfficeSlug),
            initialWindowCount: @js($serviceWindowCountSelection),
            offices: @js(
                $offices
                    ->map(fn ($office) => [
                        'slug' => $office->slug,
                        'name' => $office->name,
                        'windowCount' => $office->resolvedServiceWindowCount(),
                    ])
                    ->values()
                    ->all()
            ),
        })"
    >
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <h2 id="service-window-setup-heading" class="lgu-section-title">Service Window Setup</h2>
                <p class="mt-1 text-sm text-slate-500">Choose which public office to update and set how many service windows it should use.</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <span class="font-semibold text-slate-900" x-text="selectedOfficeLabel">{{ $serviceWindowSelectedOfficeLabel }}</span>
                currently uses
                <span class="font-semibold text-blue-700" x-text="selectedOfficeCurrentCount">{{ $serviceWindowCurrentCount }}</span>
                window<span x-text="selectedOfficeCurrentCount === 1 ? '' : 's'">{{ $serviceWindowCurrentCount === 1 ? '' : 's' }}</span>.
            </div>
        </div>

        <form
            x-on:submit.prevent="$wire.updateServiceWindowCount(selectedOfficeSlug, selectedWindowCount)"
            class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-[minmax(240px,1fr)_minmax(180px,220px)_auto] xl:items-end"
        >
            <div>
                <label for="service-window-office" class="mb-2 block text-sm font-medium text-slate-700">Office</label>
                <select
                    id="service-window-office"
                    x-model="selectedOfficeSlug"
                    wire:model.live="serviceWindowOfficeSlug"
                    x-on:change="syncWindowCount()"
                    @disabled($offices->isEmpty())
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-slate-100"
                >
                    @forelse($offices as $office)
                        <option value="{{ $office->slug }}">{{ $office->name }}</option>
                    @empty
                        <option value="">No public offices available</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label for="service-window-count" class="mb-2 block text-sm font-medium text-slate-700">Service Windows</label>
                <select
                    id="service-window-count"
                    x-model="selectedWindowCount"
                    @disabled($offices->isEmpty())
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-slate-100"
                >
                    @foreach($serviceWindowCountOptions as $windowCountOption)
                        <option value="{{ $windowCountOption }}">
                            {{ $windowCountOption }} window{{ $windowCountOption === 1 ? '' : 's' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="updateServiceWindowCount"
                @disabled($offices->isEmpty())
                class="lgu-btn inline-flex items-center justify-center rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-blue-300"
            >
                Apply Window Count
            </button>
        </form>

        @if($selectedOffice)
            <form wire:submit="saveServiceWindowLabels" class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <h3 class="text-base font-semibold text-slate-900">Service Window Labels</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Rename the tabs for {{ $selectedOffice->name }}. Leave a field blank to use the default window name.
                        </p>
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="saveServiceWindowLabels"
                        class="lgu-btn inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-slate-400"
                    >
                        Save Window Labels
                    </button>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($selectedOffice->serviceWindowNumbers() as $windowNumber)
                        <div>
                            <label for="service-window-label-{{ $windowNumber }}" class="mb-2 block text-sm font-medium text-slate-700">
                                Window {{ $windowNumber }}
                            </label>
                            <input
                                id="service-window-label-{{ $windowNumber }}"
                                type="text"
                                wire:model.defer="serviceWindowLabels.{{ $windowNumber }}"
                                maxlength="40"
                                placeholder="Window {{ $windowNumber }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                            @error('serviceWindowLabels.'.$windowNumber)
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </form>
        @endif
    </section>

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

            <form wire:submit="createOffice" autocomplete="off" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="office-name" class="mb-2 block text-sm font-medium text-slate-700">Office Name</label>
                    <input
                        id="office-name"
                        type="text"
                        wire:model.live="officeName"
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

                <div>
                    <label for="office-admin-email" class="mb-2 block text-sm font-medium text-slate-700">Office Login Email</label>
                    <input
                        id="office-admin-email"
                        type="email"
                        wire:model.live="officeAdminEmail"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="e.g. citizen.center@manolofortich.gov.ph"
                    >
                    <p class="mt-2 text-xs text-slate-500">Auto-suggested from the office name, but you can replace it.</p>
                    @error('officeAdminEmail')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="office-admin-password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <input
                            id="office-admin-password"
                            type="password"
                            wire:model="officeAdminPassword"
                            autocomplete="new-password"
                            autocapitalize="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-20 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            placeholder="Create a password"
                        >
                        <button
                            type="button"
                            data-password-toggle
                            data-password-target="office-admin-password"
                            aria-controls="office-admin-password"
                            aria-label="Show password"
                            aria-pressed="false"
                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-xs font-semibold text-blue-700 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                        >
                            Show
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Use at least 8 characters. The office can change it later after logging in.</p>
                    @error('officeAdminPassword')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="office-admin-password-confirmation" class="mb-2 block text-sm font-medium text-slate-700">Confirm Password</label>
                    <div class="relative">
                        <input
                            id="office-admin-password-confirmation"
                            type="password"
                            wire:model="officeAdminPasswordConfirmation"
                            autocomplete="new-password"
                            autocapitalize="off"
                            spellcheck="false"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-20 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            placeholder="Retype the password"
                        >
                        <button
                            type="button"
                            data-password-toggle
                            data-password-target="office-admin-password-confirmation"
                            aria-controls="office-admin-password-confirmation"
                            aria-label="Show password confirmation"
                            aria-pressed="false"
                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-xs font-semibold text-blue-700 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                        >
                            Show
                        </button>
                    </div>
                    @error('officeAdminPasswordConfirmation')
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
                        <th class="px-6 py-3 font-semibold">Service Windows</th>
                        <th class="px-6 py-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offices as $office)
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $office->name }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $office->display_name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $office->prefix }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $office->resolvedServiceWindowCount() }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        wire:click="resetNumbering({{ $office->id }})"
                                        wire:confirm="Reset queue numbering for {{ $office->name }} to 1? This will clear this office's generated tickets for today."
                                        class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100"
                                    >
                                        Reset Queue #
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="promptDeleteOffice({{ $office->id }})"
                                        class="inline-flex items-center rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No public queue offices are configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($officeIdPendingDeletion)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-office-title"
            wire:click.self="cancelDeleteOffice"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-600">Delete Office</p>
                <h3 id="delete-office-title" class="mt-2 text-xl font-semibold text-slate-900">
                    Delete {{ $officeNamePendingDeletion }}?
                </h3>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    This will remove the office, delete its queue entries, and delete any linked users. This action cannot be undone.
                </p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        wire:click="cancelDeleteOffice"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        wire:click="deleteOffice"
                        wire:loading.attr="disabled"
                        wire:target="deleteOffice,cancelDeleteOffice"
                        class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-rose-300"
                    >
                        Delete Office
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@once
    <script>
        function serviceWindowSetup({ initialOfficeSlug, initialWindowCount, offices }) {
            return {
                offices,
                selectedOfficeSlug: initialOfficeSlug || (offices[0]?.slug ?? ''),
                selectedWindowCount: initialWindowCount || '1',
                init() {
                    if (! this.selectedOffice && this.offices.length > 0) {
                        this.selectedOfficeSlug = this.offices[0].slug;
                        this.syncWindowCount();
                    }

                    if (! this.selectedWindowCount) {
                        this.syncWindowCount();
                    }
                },
                get selectedOffice() {
                    return this.offices.find((office) => office.slug === this.selectedOfficeSlug) ?? this.offices[0] ?? null;
                },
                get selectedOfficeLabel() {
                    return this.selectedOffice?.name ?? 'No office selected';
                },
                get selectedOfficeCurrentCount() {
                    return Number(this.selectedOffice?.windowCount ?? 1);
                },
                syncWindowCount() {
                    this.selectedWindowCount = String(this.selectedOffice?.windowCount ?? 1);
                },
            };
        }

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-password-toggle]');

            if (! toggle) {
                return;
            }

            const targetId = toggle.getAttribute('data-password-target');
            const input = targetId ? document.getElementById(targetId) : null;

            if (! input) {
                return;
            }

            const shouldShow = input.type === 'password';

            input.type = shouldShow ? 'text' : 'password';
            toggle.textContent = shouldShow ? 'Hide' : 'Show';
            toggle.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
            toggle.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
        });
    </script>
@endonce
