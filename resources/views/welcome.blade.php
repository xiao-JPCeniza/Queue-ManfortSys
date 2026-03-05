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
            gap: 10px;
            justify-content: center;
        }

        .lgu-logo {
            height: 54px;
            width: auto;
            flex-shrink: 0;
        }

        .header-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 12px 28px;
            text-align: center;
        }

        .header-info-grid {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 10px 20px;
        }

        .header-info-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: left;
        }

        .header-info-item svg {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
        }

        .header-info-item p {
            margin: 0;
            color: #0f172a;
            text-shadow: none;
        }

        @media (max-width: 640px) {
            .lgu-logo {
                height: 46px;
            }

            .header-info-item {
                width: 100%;
                justify-content: center;
            }
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

        .main-background .text-center {
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
            <div class="max-w-screen-2xl mx-auto px-4 py-2.5">
                <div class="header-content">
                    <div class="lgu-brand">
                        <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich logo" class="lgu-logo">
                        <div class="leading-tight text-left">
                            <p class="text-xs sm:text-sm text-slate-700">Local Government Unit of</p>
                            <h1 class="text-3xl lg:text-4xl leading-[0.95] font-extrabold">Manolo Fortich</h1>
                        </div>
                    </div>

                    <div class="header-info-grid">
                        <div class="header-info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M8 2v3M16 2v3M4 8h16M5 4h14a1 1 0 0 1 1 1v14a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V5a1 1 0 0 1 1-1Z" />
                                <circle cx="16.5" cy="16.5" r="4.5" />
                                <path d="M16.5 14v2.5l1.5 1" />
                            </svg>
                            <div>
                                <p class="font-extrabold text-lg leading-tight">PH Standard Time</p>
                                <p id="ph-time" class="text-base leading-tight">--/--/---- | --:--:-- --</p>
                            </div>
                        </div>

                        <div class="header-info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M21 15.5v2a2 2 0 0 1-2.2 2 16.5 16.5 0 0 1-7.2-2.6 16 16 0 0 1-5-5A16.5 16.5 0 0 1 4 4.7 2 2 0 0 1 6 2.5h2a2 2 0 0 1 2 1.7c.1 1 .4 2 .8 2.9a2 2 0 0 1-.5 2.1l-.9.9a13 13 0 0 0 5 5l.9-.9a2 2 0 0 1 2.1-.5c.9.4 1.9.7 2.9.8a2 2 0 0 1 1.7 2Z" />
                            </svg>
                            <div>
                                <p class="font-extrabold text-lg leading-tight">Contact Us</p>
                                <p class="text-base leading-tight">+63 917 724 3823</p>
                            </div>
                        </div>

                        <div class="header-info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="2.5" y="4" width="19" height="16" rx="2" />
                                <path d="m3.5 6 8.5 6 8.5-6M14 13l7 7M3 20l6-6" />
                            </svg>
                            <div>
                                <p class="font-extrabold text-lg leading-tight">Email Us</p>
                                <p class="text-base leading-tight">mmo@manolofortich.gov.ph</p>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>
            </div>
        </header>
        <main
            class="main-background flex-1 flex flex-col"
            role="main"
            style="{{ $municipalBgAsset ? '--municipal-bg-image: url(\''.$municipalBgAsset.'\');' : '' }}"
        >
            <div class="flex-1 flex items-center justify-center p-6">
                <div class="text-center max-w-2xl">
                    <h2 class="text-3xl font-bold text-slate-800 mb-4">Welcome to the LGU Queue System</h2>
                    <p class="text-slate-600 mb-8">
                        Get a queue number by selecting your office (MENRO, MISO, MAO, etc.). Your ticket will be announced by voice and shown in a pop-up.
                        Staff may log in to manage queues and serve clients.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="{{ route('queue.client') }}" class="lgu-btn px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Get your Queue Number
                        </a>
                   
                    </div>
                </div>
            </div>

            <footer class="relative z-10 py-4 text-center text-slate-500 text-sm">
                <div class="max-w-6xl mx-auto px-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="sm:text-left">Copyright &copy; 2026 <span>LGU Manolo Fortich Website</span>, All Rights Reserved.</p>
                    <p class="sm:text-right">Developed By <span>Management Information Systems Office</span></p>
                </div>
            </footer>
        </main>
    </div>
    <script>
        (() => {
            const timeElement = document.getElementById('ph-time');
            if (!timeElement) return;

            const getPart = (parts, type) => parts.find((part) => part.type === type)?.value ?? '';
            const formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });

            const updateTime = () => {
                const parts = formatter.formatToParts(new Date());
                const month = getPart(parts, 'month');
                const day = getPart(parts, 'day');
                const year = getPart(parts, 'year');
                const hour = getPart(parts, 'hour');
                const minute = getPart(parts, 'minute');
                const second = getPart(parts, 'second');
                const period = getPart(parts, 'dayPeriod');
                timeElement.textContent = `${month}/${day}/${year} | ${hour}:${minute}:${second} ${period}`;
            };

            updateTime();
            window.setInterval(updateTime, 1000);
        })();
    </script>
</body>
</html>
