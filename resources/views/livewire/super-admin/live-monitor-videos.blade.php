@php
    $videoCount = $videos->count();
    $activeVideoName = $activeVideo['original_name'] ?? 'Default tourism video';
    $hasUploadError = $errors->has('idleMonitorVideoUpload');
@endphp

<div
    x-data="liveMonitorVideoManager({ openOnLoad: @js($hasUploadError) })"
    class="space-y-6"
>
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-gradient-to-b from-slate-50 via-white to-slate-100 shadow-[0_24px_60px_rgba(15,23,42,0.08)]">
        <div class="space-y-6 p-6 sm:p-8">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-4xl font-black tracking-[-0.04em] text-slate-700 sm:text-5xl">Live Monitor Videos</h1>
                    <p class="mt-3 text-base leading-7 text-slate-500">
                        Upload and manage the videos shown on the live monitor during idle time. The selected start video plays first, then the rest of the library follows automatically.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="{{ $playlistPreviewUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Preview Playlist
                    </a>

                    <button
                        type="button"
                        x-on:click="openUploadModal()"
                        class="inline-flex min-h-12 items-center justify-center rounded-2xl px-5 text-sm font-semibold text-white transition whitespace-nowrap"
                        style="background: linear-gradient(135deg, #db2777, #ec4899); box-shadow: 0 12px 24px rgba(219, 39, 119, 0.25);"
                    >
                        + Add Video
                    </button>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <label class="flex w-full max-w-xl items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                    <input
                        type="search"
                        x-model.trim="search"
                        placeholder="Search by filename or status..."
                        class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                    >
                </label>

                <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-500">
                    <span>{{ number_format($videoCount) }} uploaded {{ \Illuminate\Support\Str::plural('video', $videoCount) }}</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-pink-500"></span>
                    <span>Playlist starts with: {{ $activeVideoName }}</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border-t border-slate-200 bg-white">
            <table class="min-w-full w-full table-fixed text-left">
                <thead class="bg-gradient-to-b from-emerald-50 to-slate-50">
                    <tr class="text-xs uppercase tracking-[0.14em] text-slate-500">
                        <th class="w-[32%] px-6 py-4 font-extrabold">Video</th>
                        <th class="w-[34%] px-6 py-4 font-extrabold">Details</th>
                        <th class="w-[16%] px-6 py-4 font-extrabold">Status</th>
                        <th class="w-[18%] px-6 py-4 font-extrabold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($videos as $video)
                        @php
                            $uploadedAt = \Illuminate\Support\Carbon::parse($video['uploaded_at'])->setTimezone('Asia/Manila');
                            $sizeInMb = number_format(((int) ($video['size_bytes'] ?? 0)) / 1048576, 2);
                            $searchTokens = strtolower(implode(' ', [
                                'idle monitor video playlist',
                                $video['original_name'] ?? '',
                                $video['is_active'] ? 'starts playlist' : 'in playlist',
                            ]));
                        @endphp
                        <tr x-show="matchesSearch(@js($searchTokens))" x-transition.opacity.duration.150ms class="hover:bg-slate-50/70">
                            <td class="px-6 py-5 align-middle">
                                <div class="relative w-full max-w-[18rem] overflow-hidden rounded-[1.35rem] bg-slate-900 shadow-inner">
                                    <video muted playsinline preload="metadata" class="aspect-video w-full object-cover opacity-90">
                                        <source src="{{ route('super-admin.live-monitor-videos.preview', $video['id']) }}" type="video/mp4">
                                    </video>
                                    <span class="absolute bottom-2 right-2 rounded-full bg-slate-900/70 px-2 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-white">MP4</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 align-middle">
                                <div class="max-w-[22rem] space-y-2">
                                    <p class="break-all text-[1.2rem] font-bold leading-tight text-slate-700">{{ $video['original_name'] }}</p>
                                    <p class="text-sm leading-6 font-medium text-slate-400">
                                        Updated {{ $uploadedAt->diffForHumans() }} | {{ $uploadedAt->format('M d, Y h:i A') }} Manila time | {{ $sizeInMb }} MB
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-5 align-middle">
                                <span class="inline-flex rounded-full px-4 py-2 text-sm font-extrabold uppercase tracking-[0.08em] {{ $video['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                                    {{ $video['is_active'] ? 'Starts Playlist' : 'In Playlist' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 align-middle">
                                <div class="inline-flex overflow-hidden rounded-2xl border border-slate-300 bg-white">
                                    <a
                                        href="{{ route('super-admin.live-monitor-videos.preview', $video['id']) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-11 w-12 items-center justify-center border-r border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                                        title="Preview video"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M15 3h6v6"></path>
                                            <path d="M10 14 21 3"></path>
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        </svg>
                                    </a>

                                    @if(! $video['is_active'])
                                        <button
                                            type="button"
                                            wire:click="setActiveVideo('{{ $video['id'] }}')"
                                            class="inline-flex h-11 w-12 items-center justify-center border-r border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                                            title="Set as first video in playlist"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="m5 12 5 5L20 7"></path>
                                            </svg>
                                        </button>
                                    @endif

                                    <button
                                        type="button"
                                        wire:click="deleteVideo('{{ $video['id'] }}')"
                                        wire:confirm="Delete this uploaded live monitor video from the library?"
                                        class="inline-flex h-11 w-12 items-center justify-center text-rose-500 transition hover:bg-rose-50 hover:text-rose-600"
                                        title="Delete video"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M3 6h18"></path>
                                            <path d="M8 6V4h8v2"></path>
                                            <path d="M19 6l-1 14H6L5 6"></path>
                                            <path d="M10 11v6"></path>
                                            <path d="M14 11v6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <p class="text-lg font-bold text-slate-700">No uploaded idle videos yet.</p>
                                <p class="mt-2 text-sm text-slate-500">The live monitor is currently using the default tourism video until you upload a playlist video.</p>
                            </td>
                        </tr>
                    @endforelse

                    @if($videos->isNotEmpty())
                        <tr x-show="hasSearch && !hasVisibleRows()" x-transition.opacity.duration.150ms>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <p class="text-lg font-bold text-slate-700">No videos match your search.</p>
                                <p class="mt-2 text-sm text-slate-500">Try a different filename or clear the search field.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <div
        x-cloak
        x-show="showUploadModal"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 bg-slate-950/35 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="create-live-monitor-video-title"
        x-on:keydown.escape.window="closeUploadModal()"
    >
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6" x-on:click.self="closeUploadModal()">
            <section class="w-full max-w-5xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.22)]">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-6 sm:px-9">
                    <div>
                        <h2 id="create-live-monitor-video-title" class="text-4xl font-black tracking-[-0.04em] text-slate-700">Upload New Video</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">
                            Add a new MP4 file to the live monitor library. It becomes the first video in the idle playlist after the upload fully finishes.
                        </p>
                    </div>

                    <button
                        type="button"
                        x-on:click="closeUploadModal()"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                        aria-label="Close upload dialog"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>

                <form
                    action="{{ route('super-admin.live-monitor-videos.upload') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="space-y-6 px-6 py-6 sm:px-9 sm:py-8"
                    x-on:submit.prevent="submitUpload($event)"
                >
                    @csrf
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-500">Section</label>
                            <input
                                type="text"
                                value="Idle Monitor Video"
                                disabled
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-base text-slate-400"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-500">Active Destination</label>
                            <input
                                type="text"
                                value="Live monitor idle screen"
                                disabled
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-base text-slate-400"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="idle-monitor-video-upload" class="mb-2 block text-sm font-semibold text-slate-500">Video File</label>
                        <input
                            id="idle-monitor-video-upload"
                            type="file"
                            name="idleMonitorVideoUpload"
                            accept="video/mp4"
                            x-ref="uploadInput"
                            x-on:change="clearUploadFeedback()"
                            class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-4 file:py-2.5 file:font-semibold file:text-indigo-700 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                        >
                        <p class="mt-3 text-sm text-slate-500">Accepted format: MP4. Max 3 GB. Upload speed still depends on your PHP, server, and network limits.</p>
                        <p class="mt-1 text-sm text-slate-500">The current playlist keeps running on live monitors until this upload finishes and becomes the first video.</p>
                        @if($hasServerUploadLimitMismatch)
                            <p class="mt-1 text-sm font-semibold text-amber-600">
                                Current PHP upload limit on this server: {{ $serverUploadLimitLabel }}. Increase `upload_max_filesize` and `post_max_size` to use the full 3 GB allowance.
                            </p>
                        @endif
                        @php($idleMonitorVideoUploadError = $errors->first('idleMonitorVideoUpload'))
                        @if($idleMonitorVideoUploadError !== '')
                            <p class="mt-3 text-sm font-semibold text-rose-600">{{ $idleMonitorVideoUploadError }}</p>
                        @endif
                        <p
                            x-cloak
                            x-show="uploadError !== ''"
                            x-text="uploadError"
                            class="mt-3 text-sm font-semibold text-rose-600"
                        ></p>
                    </div>

                    <div x-show="isUploading" x-transition.opacity.duration.150ms class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <div class="mb-3 flex items-center justify-between gap-3 text-sm font-semibold text-slate-600">
                            <span x-text="uploadStatusLabel"></span>
                            <span x-text="progressLabel"></span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-pink-600 to-fuchsia-500 transition-all duration-200"
                                x-bind:style="`width: ${uploadProgress}%`"
                            ></div>
                        </div>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500" x-text="transferLabel"></p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <button
                            type="button"
                            x-on:click="closeUploadModal()"
                            class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-400 px-5 text-sm font-semibold text-white transition hover:bg-slate-500"
                        >
                            Close
                        </button>
                        <button
                            type="submit"
                            x-bind:disabled="isUploading"
                            class="inline-flex min-h-12 items-center justify-center rounded-2xl px-5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-70"
                            style="background: linear-gradient(135deg, #db2777, #ec4899); box-shadow: 0 12px 24px rgba(219, 39, 119, 0.25);"
                        >
                            Save
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

@once
    <script>
        function liveMonitorVideoManager({ openOnLoad = false } = {}) {
            return {
                showUploadModal: openOnLoad,
                search: '',
                isUploading: false,
                uploadProgress: 0,
                uploadError: '',
                uploadStatusLabel: 'Uploading video...',
                uploadedBytes: 0,
                totalBytes: 0,
                openUploadModal() {
                    this.resetUploadState();
                    this.showUploadModal = true;
                },
                closeUploadModal() {
                    if (this.isUploading) {
                        return;
                    }

                    this.resetUploadState();
                    this.showUploadModal = false;
                },
                clearUploadFeedback() {
                    this.uploadError = '';
                },
                resetUploadState() {
                    this.isUploading = false;
                    this.uploadProgress = 0;
                    this.uploadError = '';
                    this.uploadStatusLabel = 'Uploading video...';
                    this.uploadedBytes = 0;
                    this.totalBytes = 0;
                },
                submitUpload(event) {
                    if (this.isUploading) {
                        return;
                    }

                    const form = event.target;
                    const file = this.$refs.uploadInput?.files?.[0] ?? null;

                    this.uploadError = '';

                    if (! file) {
                        this.uploadError = 'Please choose an MP4 video to upload.';

                        return;
                    }

                    this.isUploading = true;
                    this.uploadProgress = 0;
                    this.uploadStatusLabel = 'Uploading video...';
                    this.uploadedBytes = 0;
                    this.totalBytes = file.size || 0;

                    const request = new XMLHttpRequest();

                    request.open(form.method || 'POST', form.action, true);
                    request.responseType = 'json';
                    request.setRequestHeader('Accept', 'application/json');
                    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    request.upload.addEventListener('progress', (uploadEvent) => {
                        if (! uploadEvent.lengthComputable || uploadEvent.total === 0) {
                            return;
                        }

                        this.totalBytes = uploadEvent.total;
                        this.uploadedBytes = uploadEvent.loaded;
                        this.uploadProgress = Math.min(100, Math.round((uploadEvent.loaded / uploadEvent.total) * 100));
                        this.uploadStatusLabel = this.uploadProgress >= 100 ? 'Finalizing upload...' : 'Uploading video...';
                    });

                    request.addEventListener('load', () => {
                        const payload = this.parseUploadResponse(request);

                        if (request.status >= 200 && request.status < 300) {
                            this.uploadProgress = 100;
                            this.uploadedBytes = this.totalBytes || this.uploadedBytes;
                            this.uploadStatusLabel = 'Upload complete';

                            window.location.assign(payload.redirect_url || window.location.href);

                            return;
                        }

                        this.isUploading = false;
                        this.uploadProgress = 0;
                        this.uploadedBytes = 0;
                        this.totalBytes = 0;
                        this.uploadStatusLabel = 'Uploading video...';
                        this.uploadError = this.resolveUploadError(request, payload);
                    });

                    request.addEventListener('error', () => {
                        this.isUploading = false;
                        this.uploadProgress = 0;
                        this.uploadedBytes = 0;
                        this.totalBytes = 0;
                        this.uploadStatusLabel = 'Uploading video...';
                        this.uploadError = 'Unable to upload the video right now. Please check your network and try again.';
                    });

                    request.send(new FormData(form));
                },
                parseUploadResponse(request) {
                    if (request.response && typeof request.response === 'object') {
                        return request.response;
                    }

                    try {
                        return JSON.parse(request.responseText || '{}');
                    } catch (error) {
                        return {};
                    }
                },
                resolveUploadError(request, payload) {
                    const fieldErrors = payload?.errors?.idleMonitorVideoUpload;

                    if (Array.isArray(fieldErrors) && fieldErrors.length > 0) {
                        return fieldErrors[0];
                    }

                    if (request.status === 413) {
                        return 'The uploaded video is larger than the server allows.';
                    }

                    if (typeof payload?.message === 'string' && payload.message.trim() !== '') {
                        return payload.message;
                    }

                    return 'Unable to upload the video right now. Please try again.';
                },
                formatBytes(bytes) {
                    if (! Number.isFinite(bytes) || bytes <= 0) {
                        return '0 B';
                    }

                    const units = ['B', 'KB', 'MB', 'GB'];
                    let value = bytes;
                    let unitIndex = 0;

                    while (value >= 1024 && unitIndex < units.length - 1) {
                        value /= 1024;
                        unitIndex += 1;
                    }

                    const precision = value >= 100 || unitIndex === 0 ? 0 : 1;

                    return `${value.toFixed(precision)} ${units[unitIndex]}`;
                },
                matchesSearch(text) {
                    const query = this.search.trim().toLowerCase();

                    return query === '' || text.includes(query);
                },
                hasVisibleRows() {
                    const rows = this.$root.querySelectorAll('tbody tr[x-show]');

                    return Array.from(rows).some((row) => row.style.display !== 'none');
                },
                get hasSearch() {
                    return this.search.trim() !== '';
                },
                get progressLabel() {
                    return `${this.uploadProgress}%`;
                },
                get transferLabel() {
                    if (this.totalBytes > 0) {
                        return `${this.formatBytes(this.uploadedBytes)} of ${this.formatBytes(this.totalBytes)} uploaded`;
                    }

                    return 'Please keep this window open until the upload finishes.';
                },
            };
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endonce
