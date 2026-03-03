<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Queue System - Municipality of Manolo Fortich</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }

        .lgu-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lgu-logo {
            height: 46px;
            width: auto;
            flex-shrink: 0;
        }

        .main-background {
            position: relative;
            overflow: hidden;
            --municipal-bg-image: none;
            background:
                linear-gradient(
                    132deg,
                    #c92509 0%,
                    #e0490d 24%,
                    #f36910 39%,
                    #f6a11b 49.5%,
                    #0d5f9e 50.5%,
                    #0a4a7f 72%,
                    #07365d 100%
                );
        }

        .main-background::before {
            content: "";
            position: absolute;
            inset: -2%;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.14) 0 26%, rgba(255, 255, 255, 0) 26%) 0 0 / 300px 300px,
                linear-gradient(315deg, rgba(0, 0, 0, 0.18) 0 28%, rgba(0, 0, 0, 0) 28%) 160px 24px / 340px 340px,
                linear-gradient(123deg, rgba(255, 255, 255, 0.11) 0 24%, rgba(255, 255, 255, 0) 24%) 18px 170px / 285px 285px,
                linear-gradient(304deg, rgba(0, 0, 0, 0.2) 0 25%, rgba(0, 0, 0, 0) 25%) 390px 105px / 320px 320px,
                linear-gradient(145deg, rgba(255, 255, 255, 0.09) 0 22%, rgba(255, 255, 255, 0) 22%) 62% 14% / 300px 300px,
                linear-gradient(326deg, rgba(0, 0, 0, 0.2) 0 23%, rgba(0, 0, 0, 0) 23%) 82% 56% / 360px 360px,
                linear-gradient(to bottom, rgba(255, 255, 255, 0.05), rgba(0, 0, 0, 0.12)),
                var(--municipal-bg-image);
            background-size: auto, auto, auto, auto, auto, auto, auto, cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(0.2px);
            pointer-events: none;
            z-index: 0;
        }

        .main-background::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(900px 420px at 50% 50%, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0.04) 40%, rgba(0, 0, 0, 0.24) 100%),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.06), rgba(0, 0, 0, 0.18));
            pointer-events: none;
            z-index: 0;
        }

        .main-background > .text-center {
            position: relative;
            z-index: 1;
        }

        .main-background h2 {
            color: #f8fbff;
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.24);
        }

        .main-background p {
            color: #e7eefc;
            text-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    @php
        $municipalBgPath = public_path('images/municipal-building.jpg');
        $municipalBgAsset = file_exists($municipalBgPath) ? asset('images/municipal-building.jpg') : null;
    @endphp
    <div class="min-h-screen flex flex-col">
        <header class="bg-white text-slate-900 shadow-md border-b border-slate-200">
            <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
                <div class="lgu-brand">
                    <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich logo" class="lgu-logo">
                    <div>
                    <h1 class="text-xl font-extrabold">LGU Queue System</h1>
                    <p class="text-slate-500 text-sm">Municipality of Manolo Fortich</p>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="lgu-btn px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-white">
                    Staff login
                </a>
            </div>
        </header>

        <main
            class="main-background flex-1 flex items-center justify-center p-6"
            role="main"
            style="{{ $municipalBgAsset ? '--municipal-bg-image: url(\''.$municipalBgAsset.'\');' : '' }}"
        >
            <div class="text-center max-w-2xl">
                <h2 class="text-3xl font-bold text-slate-800 mb-4">Welcome to the LGU Queue System</h2>
                <p class="text-slate-600 mb-8">
                    Get a queue number by selecting your office (MENRO, MISO, MAO, etc.). Your ticket will be announced by voice and shown in a pop-up.
                    Staff may log in to manage queues and serve clients.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('queue.client') }}" class="lgu-btn px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        Get queue number (Client)
                    </a>
                    <a href="{{ route('login') }}" class="lgu-btn px-6 py-3.5 bg-slate-600 hover:bg-slate-700 text-white font-semibold rounded-xl transition shadow-md focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                        Staff / Admin login
                    </a>
                </div>
            </div>
        </main>

        <footer class="py-4 text-center text-slate-500 text-sm border-t border-slate-200 bg-white">
            Municipality of Manolo Fortich &copy; {{ date('Y') }} — Queue Management System
        </footer>
    </div>
</body>
</html>
