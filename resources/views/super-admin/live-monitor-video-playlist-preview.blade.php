@extends('layouts.app')

@section('title', 'Live Monitor Playlist Preview')
@section('full_width', '1')

@section('content')
    @php
        $videoLibrary = app(\App\Support\LiveMonitorVideoLibrary::class);
        $playlistVideos = $videoLibrary->playlistVideos();
        $playlistItems = $playlistVideos
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

        if ($playlistItems === []) {
            $fallbackUrl = route('media.tourism-video');
            $fallbackPath = public_path('images/MF TOURISM VIDEO.mp4');
            $fallbackRevision = is_file($fallbackPath) ? (string) filemtime($fallbackPath) : 'default';

            $playlistItems = [[
                'id' => 'default-tourism-video',
                'name' => 'MF TOURISM VIDEO.mp4',
                'url' => $fallbackUrl.(str_contains($fallbackUrl, '?') ? '&' : '?').'v='.rawurlencode($fallbackRevision),
                'revision' => $fallbackRevision,
                'is_active' => true,
            ]];
        }

        $startingVideoName = $playlistItems[0]['name'] ?? 'MF TOURISM VIDEO.mp4';
    @endphp

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 text-white shadow-[0_24px_60px_rgba(15,23,42,0.18)]">
        <div class="border-b border-white/10 bg-white/5 px-6 py-5 sm:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-3xl font-black tracking-[-0.04em] sm:text-4xl">Live Monitor Playlist Preview</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-300 sm:text-base">
                        This preview starts with <span class="font-semibold text-white">{{ $startingVideoName }}</span> and continues through the rest of the uploaded video library automatically.
                    </p>
                </div>

                <a
                    href="{{ route('super-admin.live-monitor-videos') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-5 text-sm font-semibold text-white transition hover:bg-white/15"
                >
                    Back to Library
                </a>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-black shadow-inner">
                <video
                    data-playlist-preview-player
                    controls
                    autoplay
                    muted
                    playsinline
                    preload="auto"
                    class="aspect-video w-full bg-black"
                ></video>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-300">
                <span>{{ number_format(count($playlistItems)) }} {{ \Illuminate\Support\Str::plural('video', count($playlistItems)) }} in playlist</span>
                <span class="h-1.5 w-1.5 rounded-full bg-pink-400"></span>
                <span data-playlist-preview-label>Now previewing: {{ $startingVideoName }}</span>
            </div>
        </div>
    </section>

    <script type="application/json" data-playlist-preview-items>
        @json($playlistItems)
    </script>

    @once
        <script>
            (() => {
                const player = document.querySelector('[data-playlist-preview-player]');
                const label = document.querySelector('[data-playlist-preview-label]');
                const playlistNode = document.querySelector('[data-playlist-preview-items]');

                if (! player || ! playlistNode) {
                    return;
                }

                let playlist = [];

                try {
                    const parsedPlaylist = JSON.parse(playlistNode.textContent || '[]');

                    if (Array.isArray(parsedPlaylist)) {
                        playlist = parsedPlaylist.filter((item) => item && typeof item.url === 'string' && item.url !== '');
                    }
                } catch (error) {
                    playlist = [];
                }

                if (playlist.length === 0) {
                    return;
                }

                let currentIndex = 0;

                const updatePlayer = () => {
                    const currentItem = playlist[currentIndex];

                    if (! currentItem) {
                        return;
                    }

                    player.src = currentItem.url;
                    player.load();

                    if (label) {
                        label.textContent = `Now previewing: ${currentItem.name || 'Live monitor video'}`;
                    }

                    const playPromise = player.play();

                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(() => {});
                    }
                };

                player.addEventListener('ended', () => {
                    currentIndex = (currentIndex + 1) % playlist.length;
                    updatePlayer();
                });

                updatePlayer();
            })();
        </script>
    @endonce
@endsection
