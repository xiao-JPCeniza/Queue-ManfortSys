<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="session-pulse-url" content="{{ route('session.pulse') }}">
    <title>@yield('title', 'Queue System') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo1.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo1.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo1.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        .gov-font-heading {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            letter-spacing: -0.015em;
        }
        .lgu-topbar {
            position: relative;
            overflow: visible;
            z-index: 40;
            background:
                radial-gradient(circle at top left, rgb(96 165 250 / 0.18), transparent 30%),
                linear-gradient(180deg, #566685 0%, #12367c 100%);
            border-bottom: 1px solid rgb(148 163 184 / 0.16);
        }
        .lgu-topbar::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, transparent 0%, rgb(255 255 255 / 0.08) 50%, transparent 100%);
            opacity: 0.55;
            pointer-events: none;
        }
        .lgu-topbar-shell {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 5.25rem;
            margin: 0;
            padding: 1rem 0;
            overflow: visible;
        }
        .lgu-topbar-shell::after {
            content: '';
            position: absolute;
            inset: auto 0 0.35rem;
            height: 0.2rem;
            border-radius: 999px;
            background: linear-gradient(90deg, #f8fafc 0%, #93c5fd 38%, #fbbf24 100%);
            opacity: 0.95;
            pointer-events: none;
        }
        .lgu-topbar-start,
        .lgu-topbar-end {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
        }
        .lgu-topbar-start {
            flex: 1 1 auto;
            flex-wrap: wrap;
        }
        .lgu-topbar-end {
            flex: 0 0 auto;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .lgu-topbar-trigger,
        .lgu-topbar-link,
        .lgu-topbar-account-trigger,
        .lgu-topbar-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 2.9rem;
            padding: 0.7rem 1rem;
            border-radius: 999px;
            border: 1px solid rgb(255 255 255 / 0.16);
            background: rgb(9 25 59 / 0.18);
            color: #fff;
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1;
            transition: transform 160ms ease, background-color 160ms ease, border-color 160ms ease, color 160ms ease;
        }
        .lgu-topbar-trigger:hover,
        .lgu-topbar-link:hover,
        .lgu-topbar-account-trigger:hover,
        .lgu-topbar-login:hover {
            transform: translateY(-1px);
            background: rgb(255 255 255 / 0.16);
            border-color: rgb(255 255 255 / 0.24);
        }
        .lgu-topbar-trigger:focus,
        .lgu-topbar-link:focus,
        .lgu-topbar-account-trigger:focus,
        .lgu-topbar-login:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgb(255 255 255 / 0.18), 0 0 0 6px rgb(147 197 253 / 0.28);
        }
        .lgu-topbar-link-active {
            background: #f8fafc;
            border-color: rgb(255 255 255 / 0.3);
            color: #163574;
            box-shadow: 0 12px 24px -22px rgb(15 23 42 / 0.75);
        }
        .lgu-topbar-link-active:hover {
            background: #ffffff;
            color: #162174;
        }
        .lgu-topbar-menu {
            position: relative;
        }
        .lgu-topbar-menu[open] > summary,
        .lgu-topbar-account[open] > summary {
            background: rgb(255 255 255 / 0.16);
            border-color: rgb(255 255 255 / 0.24);
        }
        .lgu-brand-lockup {
            min-width: 0;
            display: grid;
            gap: 0.18rem;
        }
        .lgu-brand-kicker {
            margin: 0;
            color: rgb(219 234 254 / 0.78);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .lgu-brand-title {
            color: #fff;
            font-size: clamp(1.35rem, 2vw, 1.9rem);
            font-weight: 800;
            line-height: 1;
        }
        .lgu-brand-title:hover {
            color: #fff;
        }
        .lgu-brand-subtitle {
            margin: 0;
            color: rgb(191 219 254 / 0.88);
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.3;
        }
        .lgu-identity-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 999px;
            background: linear-gradient(135deg, rgb(255 255 255 / 0.26), rgb(255 255 255 / 0.08));
            border: 1px solid rgb(255 255 255 / 0.18);
            color: #fff;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            flex-shrink: 0;
        }
        .lgu-topbar-panel {
            position: absolute;
            z-index: 30;
            margin-top: 0.7rem;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid rgb(226 232 240);
            background: rgb(255 255 255 / 0.98);
            box-shadow: 0 24px 44px -28px rgb(15 23 42 / 0.35);
            backdrop-filter: blur(12px);
        }
        .lgu-topbar-panel-label {
            border-bottom: 1px solid rgb(241 245 249);
            padding: 0.75rem 1rem;
            color: rgb(100 116 139);
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .lgu-topbar-menu-panel {
            left: 0;
            width: 17rem;
        }
        .lgu-topbar-account-panel {
            right: 0;
            width: 16rem;
        }
        .lgu-topbar-panel-link,
        .lgu-topbar-panel-button {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 0.65rem;
            padding: 0.82rem 1rem;
            color: rgb(51 65 85);
            font-size: 0.92rem;
            font-weight: 600;
            text-align: left;
            transition: background-color 140ms ease, color 140ms ease;
        }
        .lgu-topbar-panel-link:hover,
        .lgu-topbar-panel-button:hover {
            background: rgb(248 250 252);
        }
        .lgu-topbar-panel-link-active {
            background: rgb(239 246 255);
            color: rgb(30 64 175);
        }
        .lgu-topbar-panel-account {
            border-bottom: 1px solid rgb(241 245 249);
            padding: 0.85rem 1rem;
        }
        .lgu-topbar-panel-account-copy {
            min-width: 0;
        }
        .lgu-topbar-panel-account-meta {
            margin: 0;
            color: rgb(100 116 139);
            font-size: 0.72rem;
        }
        .lgu-topbar-panel-account-name {
            margin: 0.15rem 0 0;
            color: rgb(30 41 59);
            font-size: 0.94rem;
            font-weight: 700;
        }
        @media (max-width: 1024px) {
            .lgu-topbar-shell {
                align-items: flex-start;
                padding-bottom: 1.15rem;
            }
            .lgu-topbar-end {
                width: 100%;
                justify-content: flex-start;
            }
        }
        @media (max-width: 720px) {
            .lgu-topbar-shell {
                padding: 0.85rem 0;
            }
            .lgu-brand-kicker,
            .lgu-brand-subtitle {
                display: none;
            }
            .lgu-topbar-end {
                gap: 0.65rem;
            }
            .lgu-topbar-panel {
                width: min(18rem, calc(100vw - 2.5rem));
            }
        }
        @media (max-width: 560px) {
            .lgu-topbar-trigger,
            .lgu-topbar-link,
            .lgu-topbar-account-trigger,
            .lgu-topbar-login {
                padding-inline: 0.9rem;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    <a href="#main-content" class="lgu-skip-link">Skip to main content</a>
    @php
        $hideNav = trim((string) $__env->yieldContent('hide_nav')) === '1';
        $fullWidth = trim((string) $__env->yieldContent('full_width')) === '1';
    @endphp
    @if(!$hideNav)
        @php
            $activeOffice = request()->attributes->get('office');
            $authUser = auth()->user();
            $isSuperAdmin = $authUser?->isSuperAdmin() ?? false;
            $specialOfficeSlugs = ['hrmo', 'business-permits', 'bplo', 'mho', 'mswdo', 'treasury', 'accounting', 'civil-registry', 'assessors-office'];
            $dashboardShortcutSlugs = $specialOfficeSlugs;
            $dashboardShortcutOfficeSlug = $authUser?->isOfficeAdmin() ? $authUser?->office?->slug : null;
            $showDashboardShortcut = $dashboardShortcutOfficeSlug && in_array($dashboardShortcutOfficeSlug, $dashboardShortcutSlugs, true);
            $isOfficeDashboard = request()->routeIs('office.dashboard') && $activeOffice;
            $supportsAdvancedOfficeMenu = $isOfficeDashboard && in_array($activeOffice->slug, $specialOfficeSlugs, true);
            $sidebarOfficeSlug = $activeOffice?->slug ?? 'hrmo';
            $currentDashboardOfficeSlug = $activeOffice?->slug ?? (string) request()->route('office');
            $activeOfficeTab = (string) request()->query('tab', 'reports');
            $isSuperAdminReports = request()->routeIs('super-admin.reports');
            $isSuperAdminQueueManagement = request()->routeIs('super-admin.queue-management');
            $isSuperAdminOffices = request()->routeIs('super-admin.offices');
            $isSuperAdminUserManagement = request()->routeIs('super-admin.user-management');
            $sidebarMenuLabel = $isSuperAdmin ? 'Super Admin Panel' : 'Office Menu';
            $sidebarMenuItems = [];

            if ($isSuperAdmin) {
                $sidebarMenuItems = [
                    [
                        'label' => 'Queue Management',
                        'href' => route('super-admin.queue-management'),
                        'active' => $isSuperAdminQueueManagement,
                    ],
                    [
                        'label' => 'Offices',
                        'href' => route('super-admin.offices'),
                        'active' => $isSuperAdminOffices,
                    ],
                    [
                        'label' => 'User Management',
                        'href' => route('super-admin.user-management'),
                        'active' => $isSuperAdminUserManagement,
                    ],
                ];
            } elseif ($isOfficeDashboard) {
                if ($supportsAdvancedOfficeMenu) {
                    $sidebarMenuItems[] = [
                        'label' => 'Queue Management',
                        'href' => route('office.dashboard', $sidebarOfficeSlug) . '?tab=queue-management',
                        'active' => $currentDashboardOfficeSlug === $sidebarOfficeSlug && $activeOfficeTab === 'queue-management',
                    ];
                }
            }

            $showAdminSidebarMenu = ! empty($sidebarMenuItems);
        @endphp
        <nav class="lgu-topbar text-white" role="navigation" aria-label="Main">
            <div class="{{ $fullWidth ? 'w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' }}">
                <div class="lgu-topbar-shell">
                    <div class="lgu-topbar-start">
                        @if($showAdminSidebarMenu)
                            <details class="lgu-topbar-menu">
                                <summary class="lgu-topbar-trigger list-none cursor-pointer [&::-webkit-details-marker]:hidden" aria-label="Open sidebar menu">
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        Menu
                                    </span>
                                </summary>
                                <div class="lgu-topbar-panel lgu-topbar-menu-panel">
                                    <div class="lgu-topbar-panel-label">
                                        {{ $sidebarMenuLabel }}
                                    </div>
                                    <nav class="py-1 text-sm" aria-label="Office Queue Navigation">
                                        @foreach($sidebarMenuItems as $menuItem)
                                            <a href="{{ $menuItem['href'] }}" wire:navigate
                                               class="lgu-topbar-panel-link {{ $menuItem['active'] ? 'lgu-topbar-panel-link-active' : '' }}">
                                                {{ $menuItem['label'] }}
                                            </a>
                                        @endforeach
                                    </nav>
                                </div>
                            </details>
                        @endif
                        <div class="lgu-brand-lockup">
                            <a href="{{ url('/') }}" wire:navigate class="lgu-brand-title focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800 rounded px-1">
                                Municipal Queue Services
                            </a>
                            @auth
                                <p class="lgu-brand-subtitle">Operations dashboard for queue monitoring and office coordination.</p>
                            @endauth
                        </div>
                    </div>

                    <div class="lgu-topbar-end">
                        @auth
<<<<<<< HEAD
                            @if(auth()->user()->isSuperAdmin())
                                <a href="{{ route('super-admin.index') }}" wire:navigate class="lgu-topbar-link {{ request()->routeIs('super-admin.index') ? 'lgu-topbar-link-active' : '' }}">Dashboard</a>
=======
                            @if(auth()->user()->isQueueMaster() || auth()->user()->isSuperAdmin())
                                @php($mainDashboardRoute = auth()->user()->isSuperAdmin() ? route('super-admin.index') : route('queue-master.index'))
                                <a href="{{ $mainDashboardRoute }}" wire:navigate class="lgu-topbar-link {{ request()->routeIs('super-admin.index') || request()->routeIs('super-admin.reports') || request()->routeIs('queue-master.index') ? 'lgu-topbar-link-active' : '' }}">Dashboard</a>
>>>>>>> fea74028e8d2e0547137d5aa634daa7a26e00abd
                            @endif
                            @if($showDashboardShortcut)
                                <a href="{{ route('office.dashboard', $dashboardShortcutOfficeSlug) }}?tab=reports" wire:navigate
                                   class="lgu-topbar-link {{ request()->routeIs('office.dashboard') && $currentDashboardOfficeSlug === $dashboardShortcutOfficeSlug && $activeOfficeTab === 'reports' ? 'lgu-topbar-link-active' : '' }}">
                                    Dashboard
                                </a>
                            @endif
                            <details class="relative lgu-topbar-account">
                                <summary class="lgu-topbar-account-trigger list-none cursor-pointer [&::-webkit-details-marker]:hidden" aria-label="Open account menu">
                                    <span class="inline-flex items-center gap-2">
                                        @if($authUser->profile_photo_url)
                                            <img src="{{ $authUser->profile_photo_url }}"
                                                 alt="{{ $authUser->name }} profile photo"
                                                 class="h-8 w-8 rounded-full border border-white/20 object-cover">
                                        @else
                                            <span class="lgu-identity-avatar h-8 w-8 text-[0.72rem]">{{ $authUser->initials }}</span>
                                        @endif
                                        <span>Account</span>
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                        </svg>
                                    </span>
                                </summary>
                                <div class="lgu-topbar-panel lgu-topbar-account-panel">
                                    @php($accountUser = $authUser)
                                    <div class="lgu-topbar-panel-account">
                                        <div class="flex items-center gap-2">
                                            @if($accountUser->profile_photo_url)
                                                <img src="{{ $accountUser->profile_photo_url }}"
                                                     alt="{{ $accountUser->name }} profile photo"
                                                     class="h-8 w-8 rounded-full border border-slate-200 object-cover">
                                            @else
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-blue-50 text-xs font-semibold text-blue-700">
                                                    {{ $accountUser->initials }}
                                                </span>
                                            @endif
                                            <div class="lgu-topbar-panel-account-copy">
                                                <p class="lgu-topbar-panel-account-meta">Signed in as</p>
                                                <p class="lgu-topbar-panel-account-name">{{ $accountUser->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('profile') }}" wire:navigate class="lgu-topbar-panel-link">Profile</a>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="lgu-topbar-panel-button">Logout</button>
                                    </form>
                                </div>
                            </details>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="lgu-topbar-login">Log in</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
    @endif
    <main id="main-content" class="{{ $hideNav ? 'h-dvh overflow-hidden p-0' : ($fullWidth ? 'w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 py-8' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8') }}" role="main">
        @if(!$hideNav && session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl text-sm" role="status">{{ session('success') }}</div>
        @endif
        @if(!$hideNav && session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl text-sm" role="alert">{{ session('error') }}</div>
        @endif
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>
    @livewireScripts
</body>
</html>
