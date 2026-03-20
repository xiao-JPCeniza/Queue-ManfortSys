@php
    $idleMonitorVideoUrl = $idleMonitorVideoUrl ?? route('media.tourism-video');
    $defaultIdleMonitorVideoPath = public_path('images/MF TOURISM VIDEO.mp4');
    $videoLibrary = app(\App\Support\LiveMonitorVideoLibrary::class);
    $customIdleMonitorVideoPath = $videoLibrary->activeVideoPath();
    $idleMonitorVideoRevision = ($customIdleMonitorVideoPath !== null && $videoLibrary->exists($customIdleMonitorVideoPath))
        ? (string) filemtime($videoLibrary->absolutePath($customIdleMonitorVideoPath))
        : (is_file($defaultIdleMonitorVideoPath) ? (string) filemtime($defaultIdleMonitorVideoPath) : 'default');
    $idleMonitorVideoSource = $idleMonitorVideoUrl.(str_contains($idleMonitorVideoUrl, '?') ? '&' : '?').'v='.rawurlencode($idleMonitorVideoRevision);
@endphp

<div
    data-live-monitor-idle-video-config
    data-idle-video-url="{{ $idleMonitorVideoUrl }}"
    data-idle-video-revision="{{ $idleMonitorVideoRevision }}"
    hidden
    aria-hidden="true"
></div>

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

            const applyVideoSource = (controller, nextUrl, nextRevision) => {
                if (! controller.video || ! controller.source || nextUrl === '') {
                    return;
                }

                const separator = nextUrl.includes('?') ? '&' : '?';
                controller.source.src = `${nextUrl}${separator}v=${encodeURIComponent(nextRevision)}`;
                controller.video.load();
                controller.video.loop = false;

                if (controller.overlay && ! controller.overlay.hidden) {
                    playVideo(controller.video);
                }

                controller.appliedVideoUrl = nextUrl;
                controller.appliedVideoRevision = nextRevision;
                controller.pendingVideoUrl = null;
                controller.pendingVideoRevision = null;
            };

            const syncVideoSource = (controller) => {
                const nextUrl = controller.config?.dataset.idleVideoUrl ?? '';
                const nextRevision = controller.config?.dataset.idleVideoRevision ?? 'default';

                if (! controller.video || ! controller.source || nextUrl === '') {
                    return;
                }

                const hasAppliedVideo = controller.appliedVideoUrl !== null;
                const sourceChanged = (
                    controller.appliedVideoUrl !== nextUrl
                    || controller.appliedVideoRevision !== nextRevision
                );

                if (! sourceChanged) {
                    return;
                }

                const shouldWaitForCurrentPlaybackToEnd = hasAppliedVideo
                    && controller.overlay
                    && ! controller.overlay.hidden;

                if (shouldWaitForCurrentPlaybackToEnd) {
                    controller.pendingVideoUrl = nextUrl;
                    controller.pendingVideoRevision = nextRevision;

                    return;
                }

                applyVideoSource(controller, nextUrl, nextRevision);
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

                    if (
                        controller.pendingVideoUrl !== null
                        && controller.pendingVideoRevision !== null
                    ) {
                        applyVideoSource(
                            controller,
                            controller.pendingVideoUrl,
                            controller.pendingVideoRevision
                        );

                        return;
                    }

                    controller.video.currentTime = 0;
                    playVideo(controller.video);
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
                        idleSince: null,
                        appliedVideoUrl: null,
                        appliedVideoRevision: null,
                        pendingVideoUrl: null,
                        pendingVideoRevision: null,
                        boundVideo: null,
                    };

                    controllers.set(root, controller);
                }

                controller.config = root.querySelector('[data-live-monitor-idle-video-config]');
                controller.overlay = root.querySelector('[data-live-monitor-idle-video]');
                controller.video = root.querySelector('[data-live-monitor-idle-video-player]');
                controller.source = root.querySelector('[data-live-monitor-idle-video-source]');
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
