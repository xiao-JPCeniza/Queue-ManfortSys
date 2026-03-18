<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="session-pulse-url" content="{{ route('session.pulse') }}">
    <title>Queue Services - Municipality of Manolo Fortich</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|merriweather:700,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(180deg, #edf3f9 0%, #e6edf6 100%);
        }

        .gov-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .gov-ribbon {
            height: 0.6rem;
            background: linear-gradient(90deg, #0038a8 0 34%, #fcd116 34% 66%, #ce1126 66% 100%);
        }

        .gov-topbar {
            position: relative;
            background: linear-gradient(180deg, #ffffff 0%, #f7fafd 100%);
            border-bottom: 1px solid #d5deea;
            box-shadow: 0 24px 42px -36px rgba(9, 29, 52, 0.85);
        }

        .gov-topbar::after {
            content: "";
            position: absolute;
            inset: auto 0 0;
            height: 3px;
            background: linear-gradient(90deg, #0f5c95 0%, #b8892e 55%, #be123c 100%);
        }

        .gov-topbar-inner {
            width: min(100%, 1600px);
            margin: 0 auto;
            padding: clamp(0.85rem, 1.45vw, 1.2rem) clamp(1rem, 1.7vw, 1.7rem);
            display: grid;
            grid-template-columns: minmax(280px, 0.82fr) minmax(0, 1.18fr);
            gap: 0.85rem 1.15rem;
            align-items: center;
        }

        .gov-brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
        }

        .gov-logo-shell {
            width: 88px;
            height: 88px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            padding: 0.35rem;
            border-radius: 999px;
            border: 1px solid #d6e0ec;
            background: linear-gradient(180deg, #ffffff 0%, #eef4fb 100%);
            box-shadow: 0 18px 32px -28px rgba(11, 33, 64, 0.9);
        }

        .gov-logo-shell img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .gov-brand-copy {
            min-width: 0;
        }

        .gov-brand-kicker {
            margin: 0;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #5a708b;
        }

        .gov-brand-title {
            margin: 0.28rem 0 0;
            font-family: 'Merriweather', Georgia, serif;
            font-size: clamp(1.85rem, 2.6vw, 2.85rem);
            line-height: 0.98;
            letter-spacing: -0.03em;
            color: #0b2140;
        }

        .gov-contact-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(170px, 1fr));
            gap: 0.65rem;
        }

        .gov-contact-card {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            min-width: 0;
            min-height: 100%;
            padding: 0.8rem 0.85rem 0.85rem;
            border-radius: 1rem;
            border: 1px solid #d9e2ef;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 14px 28px -26px rgba(11, 33, 64, 0.9);
        }

        .gov-contact-icon {
            width: 1.8rem;
            height: 1.8rem;
            color: #0f5c95;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .gov-contact-card p {
            margin: 0;
        }

        .gov-contact-label {
            font-size: 0.92rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f2039;
        }

        .gov-contact-value {
            margin-top: 0.22rem;
            font-size: 0.92rem;
            line-height: 1.38;
            color: #4a5d76;
            overflow-wrap: anywhere;
        }

        .gov-main {
            position: relative;
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: clamp(1rem, 2vw, 2rem);
            overflow: hidden;
            --municipal-bg-image: none;
        }

        .gov-main::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 16% 14%, rgba(255, 255, 255, 0.42), transparent 34%),
                linear-gradient(135deg, rgba(255, 255, 255, 0.13) 0 24%, rgba(255, 255, 255, 0) 24%) 0 0 / 340px 340px,
                linear-gradient(315deg, rgba(0, 0, 0, 0.12) 0 28%, rgba(0, 0, 0, 0) 28%) 170px 24px / 360px 360px,
                linear-gradient(128deg, #3539b5 0%, #ca6028 24%, #e07b2a 43%, #efaa31 54%, #215c95 55%, #133f6b 77%, #082744 100%),
                var(--municipal-bg-image);
            background-repeat: no-repeat;
            background-size: auto, auto, auto, cover, cover;
            background-position: center;
        }

        .gov-main::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(140deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0) 26%) 0 0 / 40% 55% no-repeat,
                linear-gradient(315deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0) 32%) 100% 0 / 44% 56% no-repeat,
                linear-gradient(180deg, rgba(8, 39, 68, 0.06), rgba(8, 39, 68, 0.28));
            pointer-events: none;
        }

        .gov-main-inner {
            position: relative;
            z-index: 1;
            width: min(100%, 1440px);
            margin: 0 auto;
            flex: 1;
            display: flex;
            align-items: center;
        }

        .gov-hero-panel {
            width: 100%;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
            gap: clamp(1rem, 2vw, 2rem);
            align-items: center;
            padding: clamp(1.5rem, 2.5vw, 2.3rem);
            border-radius: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: linear-gradient(145deg, rgba(9, 31, 56, 0.76), rgba(15, 63, 115, 0.58));
            box-shadow: 0 36px 72px -40px rgba(3, 16, 33, 0.92);
            backdrop-filter: blur(5px);
        }

        .gov-hero-copy {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .gov-hero-title {
            margin: 0;
            font-family: 'Merriweather', Georgia, serif;
            font-size: clamp(2.5rem, 4vw, 4.7rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
            color: #ffffff;
            text-shadow: 0 10px 32px rgba(2, 12, 24, 0.36);
        }

        .gov-hero-text {
            margin: 1.25rem 0 0;
            max-width: 40rem;
            font-size: clamp(1.08rem, 1.55vw, 1.42rem);
            line-height: 1.72;
            color: #eef4ff;
            text-shadow: 0 4px 18px rgba(2, 12, 24, 0.25);
        }

        .gov-hero-actions {
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .gov-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20rem;
            min-height: 4.4rem;
            padding: 0 1.9rem;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: linear-gradient(180deg, #10a56c 0%, #0a865a 100%);
            color: #ffffff;
            font-size: 1.22rem;
            font-weight: 800;
            box-shadow: 0 20px 38px -24px rgba(5, 20, 43, 0.94);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .gov-cta:hover {
            transform: translateY(-2px);
            background: linear-gradient(180deg, #12b275 0%, #0b9061 100%);
            box-shadow: 0 24px 42px -26px rgba(5, 20, 43, 0.98);
        }

        .gov-media-stage {
            position: relative;
            width: 100%;
            min-height: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(0.7rem, 1.2vw, 1rem);
            border-radius: 1.6rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.03));
            overflow: hidden;
        }

        .gov-media-stage::before {
            content: "";
            position: absolute;
            inset: 8% 18%;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .gov-media-stage::after {
            content: "";
            position: absolute;
            inset: 22%;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0) 70%);
        }

        .gov-slider {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 100%;
            aspect-ratio: 1320 / 749;
            border-radius: 1.25rem;
            overflow: hidden;
            background: rgba(5, 20, 43, 0.32);
            box-shadow: 0 28px 46px -26px rgba(4, 18, 36, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .gov-slider-track {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .gov-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 600ms ease;
        }

        .gov-slide.is-active {
            opacity: 1;
        }

        .gov-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            box-sizing: border-box;
            display: block;
        }

        .gov-slide img.gov-slide-image--contain {
            object-fit: contain;
            padding: clamp(0.75rem, 2vw, 1.5rem);
        }

        .gov-slider-dots {
            position: absolute;
            inset: auto 0 0;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.8rem;
            background: linear-gradient(180deg, rgba(7, 21, 40, 0), rgba(7, 21, 40, 0.58));
        }

        .gov-slider-dot {
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.42);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .gov-slider-dot.is-active {
            background: #ffffff;
        }

        .gov-seal-mark {
            position: relative;
            z-index: 1;
            width: min(100%, 280px);
            aspect-ratio: 1;
            border-radius: 999px;
            padding: 1rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(228, 238, 248, 0.86));
            box-shadow: 0 28px 46px -26px rgba(4, 18, 36, 0.96);
        }

        .gov-seal-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .gov-footer {
            position: relative;
            z-index: 1;
            width: min(100%, 1440px);
            margin: 0 auto;
            padding: 0 0 0.25rem;
        }

        .gov-footer-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.7rem 1rem;
            padding: 1rem 1.15rem;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(8, 29, 53, 0.6);
            color: #f8fbff;
            font-size: 0.95rem;
            box-shadow: 0 20px 42px -36px rgba(3, 16, 33, 0.95);
        }

        .gov-footer-bar p {
            margin: 0;
        }

        @media (max-width: 1180px) {
            .gov-topbar-inner,
            .gov-hero-panel {
                grid-template-columns: 1fr;
            }

            .gov-contact-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .gov-contact-grid {
                grid-template-columns: 1fr;
            }

            .gov-contact-card {
                justify-content: flex-start;
            }

            .gov-hero-panel {
                padding: 1.25rem;
            }

            .gov-cta {
                width: 100%;
                min-width: 0;
            }
        }

        @media (max-width: 640px) {
            .gov-ribbon {
                height: 0.45rem;
            }

            .gov-logo-shell {
                width: 70px;
                height: 70px;
            }

            .gov-brand-title {
                font-size: 1.7rem;
            }

            .gov-main {
                padding: 0.75rem;
            }

            .gov-main-inner {
                align-items: stretch;
            }

            .gov-hero-title {
                font-size: 2rem;
            }

            .gov-hero-text {
                font-size: 1rem;
            }

            .gov-slider {
                width: 100%;
            }

            .gov-footer-bar {
                font-size: 0.84rem;
            }
        }
    </style>
</head>
<body class="text-slate-900 antialiased min-h-screen">
    @php
        $municipalBgPath = public_path('images/municipal-building.jpg');
        $municipalBgAsset = file_exists($municipalBgPath) ? asset('images/municipal-building.jpg') : null;
        $slideAssets = collect([
            ['name' => 'slide1', 'alt' => 'Municipality of Manolo Fortich commemorative feature slide'],
            ['name' => 'slide2', 'alt' => 'Municipality of Manolo Fortich public service feature slide'],
            ['name' => 'slide3', 'alt' => 'Municipality of Manolo Fortich local government feature slide'],
            ['name' => 'MTO', 'alt' => 'Municipal Treasurer\'s Office service information slide', 'fit' => 'contain'],
            ['name' => 'MSWDO', 'alt' => 'Municipal Social Welfare and Development Office service information slide', 'fit' => 'contain'],
        ])
            ->map(function (array $slide) {
                foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
                    $path = public_path("images/{$slide['name']}.{$extension}");
                    if (file_exists($path)) {
                        return [
                            'src' => asset("images/{$slide['name']}.{$extension}"),
                            'alt' => $slide['alt'],
                            'fit' => $slide['fit'] ?? 'cover',
                        ];
                    }
                }

                return null;
            })
            ->filter()
            ->values();
    @endphp
    <div class="gov-page">
        <div class="gov-ribbon" aria-hidden="true"></div>

        <header class="gov-topbar">
            <div class="gov-topbar-inner">
                <div class="gov-brand">
                    <div class="gov-logo-shell">
                        <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich logo">
                    </div>

                    <div class="gov-brand-copy">
                        <p class="gov-brand-kicker">Local Government Unit of</p>
                        <h1 class="gov-brand-title">Manolo Fortich</h1>
                    </div>
                </div>

                <div class="gov-contact-grid">
                    <article class="gov-contact-card" aria-label="Philippine standard time">
                        <svg class="gov-contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M8 2v3M16 2v3M4 8h16M5 4h14a1 1 0 0 1 1 1v14a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V5a1 1 0 0 1 1-1Z" />
                            <circle cx="16.5" cy="16.5" r="4.5" />
                            <path d="M16.5 14v2.5l1.5 1" />
                        </svg>

                        <div>
                            <p class="gov-contact-label">PH Standard Time</p>
                            <p id="ph-time" class="gov-contact-value">--/--/---- | --:--:-- --</p>
                        </div>
                    </article>

                    <article class="gov-contact-card" aria-label="Contact number">
                        <svg class="gov-contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M21 15.5v2a2 2 0 0 1-2.2 2 16.5 16.5 0 0 1-7.2-2.6 16 16 0 0 1-5-5A16.5 16.5 0 0 1 4 4.7 2 2 0 0 1 6 2.5h2a2 2 0 0 1 2 1.7c.1 1 .4 2 .8 2.9a2 2 0 0 1-.5 2.1l-.9.9a13 13 0 0 0 5 5l.9-.9a2 2 0 0 1 2.1-.5c.9.4 1.9.7 2.9.8a2 2 0 0 1 1.7 2Z" />
                        </svg>

                        <div>
                            <p class="gov-contact-label">Contact Us</p>
                            <p class="gov-contact-value">+63 917 724 3823</p>
                        </div>
                    </article>

                    <article class="gov-contact-card" aria-label="Email address">
                        <svg class="gov-contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="2.5" y="4" width="19" height="16" rx="2" />
                            <path d="m3.5 6 8.5 6 8.5-6M14 13l7 7M3 20l6-6" />
                        </svg>

                        <div>
                            <p class="gov-contact-label">Email Us</p>
                            <p class="gov-contact-value">mmo@manolofortich.gov.ph</p>
                        </div>
                    </article>
                </div>
            </div>
        </header>

        <main
            class="gov-main"
            role="main"
            style="{{ $municipalBgAsset ? '--municipal-bg-image: url(\''.$municipalBgAsset.'\');' : '' }}"
        >
            <div class="gov-main-inner">
                <section class="gov-hero-panel">
                    <div class="gov-hero-copy">
                        <h2 class="gov-hero-title">Welcome to the LGU Queue System</h2>
                        <p class="gov-hero-text">
                            Get a queue number by selecting the office you need to visit. Your ticket will be generated and announced by voice.
                            Staff may log in to manage queues and serve clients.
                        </p>

                        <div class="gov-hero-actions">
                            <a href="{{ route('queue.client') }}" wire:navigate class="gov-cta">
                                Get your Queue Number
                            </a>
                        </div>
                    </div>

                    <div class="gov-media-stage" aria-hidden="true">
                        @if($slideAssets->isNotEmpty())
                            <div class="gov-slider" data-gov-slider>
                                <div class="gov-slider-track">
                                    @foreach($slideAssets as $slideAsset)
                                        <figure class="gov-slide {{ $loop->first ? 'is-active' : '' }}" data-gov-slide>
                                            <img
                                                src="{{ $slideAsset['src'] }}"
                                                alt="{{ $slideAsset['alt'] }}"
                                                class="{{ $slideAsset['fit'] === 'contain' ? 'gov-slide-image--contain' : '' }}"
                                            >
                                        </figure>
                                    @endforeach
                                </div>

                                <div class="gov-slider-dots" aria-hidden="true">
                                    @foreach($slideAssets as $slideAsset)
                                        <span class="gov-slider-dot {{ $loop->first ? 'is-active' : '' }}" data-gov-dot></span>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="gov-seal-mark">
                                <img src="{{ asset('images/lgu-logo.png') }}" alt="">
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <footer class="gov-footer">
                <div class="gov-footer-bar">
                    <p>Copyright &copy; 2026 <span>LGU Manolo Fortich Website</span>, All Rights Reserved.</p>
                    <p>Developed By <span>Management Information Systems Office</span></p>
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

        (() => {
            const slider = document.querySelector('[data-gov-slider]');
            if (!slider) return;

            const slides = Array.from(slider.querySelectorAll('[data-gov-slide]'));
            const dots = Array.from(slider.querySelectorAll('[data-gov-dot]'));
            if (slides.length <= 1) return;

            let activeIndex = 0;

            const showSlide = (index) => {
                slides.forEach((slide, slideIndex) => {
                    slide.classList.toggle('is-active', slideIndex === index);
                });

                dots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('is-active', dotIndex === index);
                });
            };

            window.setInterval(() => {
                activeIndex = (activeIndex + 1) % slides.length;
                showSlide(activeIndex);
            }, 4000);
        })();
    </script>
    @livewireScripts
</body>
</html>
