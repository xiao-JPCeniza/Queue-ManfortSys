<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php($lockQueueZoom = request()->routeIs('queue.client'))
    @php($isPublicLiveMonitor = request()->routeIs('live-monitor.public'))
    <meta charset="utf-8">
    <meta name="viewport" content="{{ $lockQueueZoom ? 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover' : 'width=device-width, initial-scale=1' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="session-pulse-url" content="{{ route('session.pulse') }}">
    <title>@yield('title', 'Queue') - Municipality of Manolo Fortich</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo1.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo1.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo1.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        @if($isPublicLiveMonitor)
        html,
        body {
            height: 100%;
            overflow: hidden;
        }
        @endif
        @if($lockQueueZoom)
        html,
        body {
            overscroll-behavior: none;
        }

        body.queue-zoom-locked {
            touch-action: manipulation;
        }
        @endif
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased {{ $isPublicLiveMonitor ? 'h-screen overflow-hidden' : 'min-h-screen' }} {{ $lockQueueZoom ? 'queue-zoom-locked' : '' }}">
    @hasSection('content')
        @yield('content')
    @else
        {{ $slot ?? '' }}
    @endif
    @livewireScripts
    @if($lockQueueZoom)
        <script>
            (function () {
                if (window.__queueZoomLockBound) {
                    return;
                }

                window.__queueZoomLockBound = true;

                document.addEventListener('gesturestart', function (event) {
                    event.preventDefault();
                }, { passive: false });

                document.addEventListener('touchmove', function (event) {
                    if (event.touches.length > 1) {
                        event.preventDefault();
                    }
                }, { passive: false });

                let lastTouchEnd = 0;

                document.addEventListener('touchend', function (event) {
                    const now = Date.now();

                    if (now - lastTouchEnd <= 300) {
                        event.preventDefault();
                    }

                    lastTouchEnd = now;
                }, { passive: false });

                document.addEventListener('wheel', function (event) {
                    if (event.ctrlKey) {
                        event.preventDefault();
                    }
                }, { passive: false });

                document.addEventListener('keydown', function (event) {
                    if ((event.ctrlKey || event.metaKey) && ['+', '-', '=', '0'].includes(event.key)) {
                        event.preventDefault();
                    }
                }, { passive: false });
            })();
        </script>
    @endif
</body>
</html>
