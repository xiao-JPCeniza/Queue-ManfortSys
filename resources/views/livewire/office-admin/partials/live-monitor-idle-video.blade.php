@php
    $idleMonitorVideoUrl = $idleMonitorVideoUrl ?? route('media.tourism-video');
    $defaultIdleMonitorVideoPath = public_path('images/MF TOURISM VIDEO.mp4');
    $videoLibrary = app(\App\Support\LiveMonitorVideoLibrary::class);
    $playlistVideos = $videoLibrary->playlistVideos();
    $customIdleMonitorVideoPath = $videoLibrary->activeVideoPath();
    $idleMonitorVideoRevision = ($customIdleMonitorVideoPath !== null && $videoLibrary->exists($customIdleMonitorVideoPath))
        ? $videoLibrary->videoRevision($customIdleMonitorVideoPath)
        : (is_file($defaultIdleMonitorVideoPath) ? (string) filemtime($defaultIdleMonitorVideoPath) : 'default');
    $idleMonitorVideoSource = $idleMonitorVideoUrl.(str_contains($idleMonitorVideoUrl, '?') ? '&' : '?').'v='.rawurlencode($idleMonitorVideoRevision);
    $idleMonitorVideoPlaylist = $playlistVideos
        ->map(function (array $video) use ($videoLibrary): array {
            $revision = $videoLibrary->videoRevision($video);
            $videoUrl = route('media.live-monitor-video', $video['id']);

            return [
                'id' => $video['id'],
                'name' => $video['original_name'] ?? 'Live monitor video',
                'url' => $videoUrl.(str_contains($videoUrl, '?') ? '&' : '?').'v='.rawurlencode($revision),
                'revision' => $revision,
                'is_active' => (bool) ($video['is_active'] ?? false),
            ];
        })
        ->values()
        ->all();

    if ($idleMonitorVideoPlaylist === []) {
        $idleMonitorVideoPlaylist = [[
            'id' => 'default-tourism-video',
            'name' => 'MF TOURISM VIDEO.mp4',
            'url' => $idleMonitorVideoSource,
            'revision' => $idleMonitorVideoRevision,
            'is_active' => true,
        ]];
    }

    $idleMonitorVideoPlaylistRevision = sha1(json_encode($idleMonitorVideoPlaylist));
@endphp

<div
    data-live-monitor-idle-video-config
    data-idle-video-url="{{ $idleMonitorVideoUrl }}"
    data-idle-video-revision="{{ $idleMonitorVideoRevision }}"
    data-idle-video-playlist-revision="{{ $idleMonitorVideoPlaylistRevision }}"
    hidden
    aria-hidden="true"
></div>

<script type="application/json" data-live-monitor-idle-video-playlist>
    @json($idleMonitorVideoPlaylist)
</script>

<div wire:ignore class="gov-monitor-idle-video" data-live-monitor-idle-video hidden aria-hidden="true">
    <video
        data-live-monitor-idle-video-player
        muted
        playsinline
        preload="metadata"
    >
        <source
            data-live-monitor-idle-video-source
            src="{{ $idleMonitorVideoSource }}"
            type="video/mp4"
        >
    </video>

    <div class="gov-monitor-idle-video-badge">
        Idle mode video
    </div>
</div>

@once
    <script>
        (() => {
            const controllers = new Map();
            const defaultDelayMs = 60_000;

            const playVideo = (video) => {
                if (! video) {
                    return;
                }

                const playPromise = video.play();

                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(() => {});
                }
            };

            const parsePlaylist = (controller) => {
                const fallbackUrl = controller.config?.dataset.idleVideoUrl ?? '';
                const fallbackRevision = controller.config?.dataset.idleVideoRevision ?? 'default';
                const playlistText = controller.playlistConfig?.textContent?.trim() ?? '';

                if (playlistText !== '') {
                    try {
                        const parsedPlaylist = JSON.parse(playlistText);

                        if (Array.isArray(parsedPlaylist)) {
                            const normalizedPlaylist = parsedPlaylist
                                .filter((item) => item && typeof item.url === 'string' && item.url.trim() !== '')
                                .map((item) => ({
                                    id: typeof item.id === 'string' ? item.id : '',
                                    name: typeof item.name === 'string' ? item.name : 'Live monitor video',
                                    url: item.url,
                                    revision: typeof item.revision === 'string' ? item.revision : 'default',
                                    is_active: item.is_active === true,
                                }));

                            if (normalizedPlaylist.length > 0) {
                                return normalizedPlaylist;
                            }
                        }
                    } catch (error) {
                    }
                }

                if (fallbackUrl === '') {
                    return [];
                }

                const separator = fallbackUrl.includes('?') ? '&' : '?';

                return [{
                    id: 'fallback-idle-video',
                    name: 'Idle mode video',
                    url: `${fallbackUrl}${separator}v=${encodeURIComponent(fallbackRevision)}`,
                    revision: fallbackRevision,
                    is_active: true,
                }];
            };

            const setPlaylistItem = (controller, playlistIndex, shouldAutoplay = true) => {
                if (! controller.video || ! controller.source || controller.appliedPlaylist.length === 0) {
                    return;
                }

                const normalizedIndex = (
                    Number.isInteger(playlistIndex)
                    && playlistIndex >= 0
                    && playlistIndex < controller.appliedPlaylist.length
                ) ? playlistIndex : 0;
                const playlistItem = controller.appliedPlaylist[normalizedIndex];

                if (! playlistItem || typeof playlistItem.url !== 'string' || playlistItem.url === '') {
                    return;
                }

                controller.currentPlaylistIndex = normalizedIndex;

                if (controller.source.src !== playlistItem.url) {
                    controller.source.src = playlistItem.url;
                }

                controller.video.load();
                controller.video.loop = false;

                if (shouldAutoplay && controller.overlay && ! controller.overlay.hidden) {
                    playVideo(controller.video);
                }
            };

            const applyPlaylist = (controller, nextPlaylist, nextPlaylistRevision) => {
                if (! controller.video || ! controller.source || nextPlaylist.length === 0) {
                    return;
                }

                const currentItemId = controller.appliedPlaylist[controller.currentPlaylistIndex]?.id ?? null;
                const nextIndex = nextPlaylist.findIndex((item) => item.id === currentItemId);

                controller.appliedPlaylist = nextPlaylist;
                controller.appliedPlaylistRevision = nextPlaylistRevision;
                controller.pendingPlaylist = null;
                controller.pendingPlaylistRevision = null;

                setPlaylistItem(controller, nextIndex >= 0 ? nextIndex : 0);
            };

            const syncVideoSource = (controller) => {
                const nextPlaylist = parsePlaylist(controller);
                const nextPlaylistRevision = controller.config?.dataset.idleVideoPlaylistRevision ?? 'default';

                if (! controller.video || ! controller.source || nextPlaylist.length === 0) {
                    return;
                }

                const hasAppliedPlaylist = controller.appliedPlaylist.length > 0;
                const playlistChanged = controller.appliedPlaylistRevision !== nextPlaylistRevision;

                if (! playlistChanged) {
                    return;
                }

                const shouldWaitForCurrentPlaybackToEnd = hasAppliedPlaylist
                    && controller.overlay
                    && ! controller.overlay.hidden;

                if (shouldWaitForCurrentPlaybackToEnd) {
                    controller.pendingPlaylist = nextPlaylist;
                    controller.pendingPlaylistRevision = nextPlaylistRevision;

                    return;
                }

                applyPlaylist(controller, nextPlaylist, nextPlaylistRevision);
            };

            const bindVideoEvents = (controller) => {
                if (! controller.video || controller.boundVideo === controller.video) {
                    return;
                }

                controller.video.loop = false;
                controller.video.addEventListener('ended', () => {
                    if (! controller.overlay || controller.overlay.hidden) {
                        return;
                    }

                    if (controller.pendingPlaylist !== null) {
                        applyPlaylist(
                            controller,
                            controller.pendingPlaylist,
                            controller.pendingPlaylistRevision ?? 'default'
                        );

                        return;
                    }

                    if (controller.appliedPlaylist.length <= 1) {
                        controller.video.currentTime = 0;
                        playVideo(controller.video);

                        return;
                    }

                    setPlaylistItem(
                        controller,
                        (controller.currentPlaylistIndex + 1) % controller.appliedPlaylist.length
                    );
                });

                controller.boundVideo = controller.video;
            };

            const hideOverlay = (controller, shouldResetIdleSince = false) => {
                if (! controller.overlay) {
                    return;
                }

                controller.overlay.hidden = true;
                controller.overlay.setAttribute('aria-hidden', 'true');
                controller.root.classList.remove('gov-monitor-root-idle-active');

                if (controller.video) {
                    controller.video.pause();

                    if (shouldResetIdleSince) {
                        controller.video.currentTime = 0;
                    }
                }

                if (shouldResetIdleSince) {
                    controller.idleSince = null;
                }
            };

            const showOverlay = (controller) => {
                if (! controller.overlay) {
                    return;
                }

                controller.overlay.hidden = false;
                controller.overlay.setAttribute('aria-hidden', 'false');
                controller.root.classList.add('gov-monitor-root-idle-active');

                playVideo(controller.video);
            };

            const syncMonitor = (root) => {
                let controller = controllers.get(root);

                if (! controller) {
                    controller = {
                        root,
                        config: null,
                        overlay: null,
                        video: null,
                        source: null,
                        playlistConfig: null,
                        idleSince: null,
                        appliedPlaylist: [],
                        appliedPlaylistRevision: null,
                        pendingPlaylist: null,
                        pendingPlaylistRevision: null,
                        currentPlaylistIndex: 0,
                        boundVideo: null,
                    };

                    controllers.set(root, controller);
                }

                controller.config = root.querySelector('[data-live-monitor-idle-video-config]');
                controller.overlay = root.querySelector('[data-live-monitor-idle-video]');
                controller.video = root.querySelector('[data-live-monitor-idle-video-player]');
                controller.source = root.querySelector('[data-live-monitor-idle-video-source]');
                controller.playlistConfig = root.querySelector('[data-live-monitor-idle-video-playlist]');
                bindVideoEvents(controller);
                syncVideoSource(controller);

                const hasCurrentTransaction = root.dataset.hasCurrentTransaction === 'true';
                const hasQueuedNextInline = root.dataset.hasQueuedNextInline === 'true';
                const parsedDelayMs = Number.parseInt(root.dataset.idleVideoDelayMs ?? '', 10);
                const delayMs = Number.isFinite(parsedDelayMs) && parsedDelayMs > 0
                    ? parsedDelayMs
                    : defaultDelayMs;

                if (hasCurrentTransaction || hasQueuedNextInline) {
                    hideOverlay(controller, true);

                    return;
                }

                if (controller.idleSince === null) {
                    controller.idleSince = Date.now();
                }

                if ((Date.now() - controller.idleSince) >= delayMs) {
                    showOverlay(controller);

                    return;
                }

                hideOverlay(controller, false);
            };

            const syncAll = () => {
                document.querySelectorAll('[data-live-monitor-root]').forEach(syncMonitor);

                controllers.forEach((controller, root) => {
                    if (! root.isConnected) {
                        controllers.delete(root);
                    }
                });
            };

            document.addEventListener('DOMContentLoaded', syncAll);
            document.addEventListener('livewire:initialized', syncAll);
            document.addEventListener('livewire:navigated', syncAll);

            syncAll();
            window.setInterval(syncAll, 1_000);
        })();
    </script>

    <style>
        .gov-monitor-root {
            position: relative;
        }

        .gov-monitor-shell {
            transition: opacity 220ms ease, visibility 220ms ease;
        }

        .gov-monitor-root.gov-monitor-root-idle-active {
            overflow: hidden;
        }

        .gov-monitor-root.gov-monitor-root-idle-active .gov-monitor-shell {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .gov-monitor-idle-video {
            position: fixed;
            inset: 0;
            z-index: 60;
            overflow: hidden;
            background: #000;
        }

        .gov-monitor-idle-video[hidden] {
            display: none !important;
        }

        .gov-monitor-idle-video video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #000;
        }

        .gov-monitor-idle-video-badge {
            position: absolute;
            right: 1.25rem;
            bottom: 1.25rem;
            border-radius: 999px;
            background: rgb(15 23 42 / 0.72);
            color: #fff;
            padding: 0.45rem 0.8rem;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 700;
            backdrop-filter: blur(8px);
        }

        @media (max-width: 640px) {
            .gov-monitor-idle-video-badge {
                right: 0.85rem;
                bottom: 0.85rem;
                font-size: 0.7rem;
            }
        }
    </style>
@endonce
