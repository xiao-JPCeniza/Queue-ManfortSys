<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Queue System') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    <a href="#main-content" class="lgu-skip-link">Skip to main content</a>
    @php($hideNav = trim((string) $__env->yieldContent('hide_nav')) === '1')
    @if(!$hideNav)
        @php($activeOffice = request()->attributes->get('office'))
        @php($authUser = auth()->user())
        @php($isSuperAdmin = $authUser?->isSuperAdmin() ?? false)
        @php($specialOfficeSlugs = ['hrmo', 'business-permits', 'bplo'])
        @php($dashboardShortcutOfficeSlug = $authUser?->isOfficeAdmin() ? $authUser?->office?->slug : null)
        @php($showDashboardShortcut = $dashboardShortcutOfficeSlug && in_array($dashboardShortcutOfficeSlug, $specialOfficeSlugs, true))
        @php($isAdvancedOfficeDashboard = request()->routeIs('office.dashboard') && $activeOffice && in_array($activeOffice->slug, $specialOfficeSlugs, true))
        @php($showAdminSidebarMenu = $isAdvancedOfficeDashboard || $isSuperAdmin)
        @php($sidebarOfficeSlug = $isAdvancedOfficeDashboard ? $activeOffice->slug : 'hrmo')
        @php($isSidebarOfficeDashboard = request()->routeIs('office.dashboard') && request()->route('office') === $sidebarOfficeSlug)
        @php($activeOfficeTab = (string) request()->query('tab', 'dashboard'))
        @php($isSuperAdminReports = request()->routeIs('super-admin.reports'))
        @php($isSuperAdminQueueReports = request()->routeIs('super-admin.queue-reports'))
        @php($isSuperAdminQueueManagement = request()->routeIs('super-admin.queue-management'))
        @php($isReportsActive = $isSuperAdmin ? $isSuperAdminReports : ($isSidebarOfficeDashboard && $activeOfficeTab === 'reports'))
        @php($isQueueManagementActive = $isSuperAdmin ? $isSuperAdminQueueManagement : ($isSidebarOfficeDashboard && $activeOfficeTab === 'queue-management'))
        <nav class="bg-blue-800 text-white shadow-md" role="navigation" aria-label="Main">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-3 flex-wrap">
                        @if($showAdminSidebarMenu)
                            <details class="relative">
                                <summary class="lgu-btn list-none cursor-pointer px-3 py-2 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800 [&::-webkit-details-marker]:hidden" aria-label="Open sidebar menu">
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        Menu
                                    </span>
                                </summary>
                                <div class="absolute left-0 z-30 mt-2 w-60 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl">
                                    <div class="border-b border-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ $isSuperAdmin ? 'Super Admin Panel' : 'Admin Panel' }}
                                    </div>
                                    <nav class="py-1 text-sm" aria-label="Office Queue Navigation">
                                        <a href="{{ $isSuperAdmin ? route('super-admin.reports') : route('office.dashboard', $sidebarOfficeSlug) . '?tab=reports' }}"
                                           class="flex items-center gap-2 px-4 py-2.5 {{ $isReportsActive ? 'bg-blue-50 text-blue-800 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                                            Reports
                                        </a>
                                        @if($isSuperAdmin)
                                            <a href="{{ route('super-admin.queue-reports') }}"
                                               class="flex items-center gap-2 px-4 py-2.5 {{ $isSuperAdminQueueReports ? 'bg-blue-50 text-blue-800 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                                                Queue Reports
                                            </a>
                                        @else
                                            <a href="{{ route('office.dashboard', $sidebarOfficeSlug) }}?tab=queue-reports"
                                               class="flex items-center gap-2 px-4 py-2.5 {{ $isSidebarOfficeDashboard && $activeOfficeTab === 'queue-reports' ? 'bg-blue-50 text-blue-800 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                                                Queue Reports
                                            </a>
                                        @endif
                                        <a href="{{ $isSuperAdmin ? route('super-admin.queue-management') : route('office.dashboard', $sidebarOfficeSlug) . '?tab=queue-management' }}"
                                           class="flex items-center gap-2 px-4 py-2.5 {{ $isQueueManagementActive ? 'bg-blue-50 text-blue-800 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}">
                                            Queue Management
                                        </a>
                                    </nav>
                                </div>
                            </details>
                        @endif
                        <a href="{{ url('/') }}" class="text-xl font-bold text-white hover:text-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800 rounded px-1">LGU Queue System</a>
                        @auth
                            <span class="text-blue-200 text-sm py-2">{{ auth()->user()->name }} <span class="text-blue-300">({{ auth()->user()->role?->name }})</span></span>
                        @endauth
                    </div>
                    <div class="flex items-center gap-2">
                        @auth
                            @if(auth()->user()->isQueueMaster() || auth()->user()->isSuperAdmin())
                                @php($mainDashboardRoute = auth()->user()->isSuperAdmin() ? route('super-admin.index') : route('queue-master.index'))
                                <a href="{{ $mainDashboardRoute }}" class="lgu-btn px-4 py-2.5 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">Dashboard</a>
                            @endif
                            @if($showDashboardShortcut)
                                <a href="{{ route('office.dashboard', $dashboardShortcutOfficeSlug) }}"
                                   class="lgu-btn px-4 py-2.5 rounded-lg {{ request()->routeIs('office.dashboard') && request()->route('office') === $dashboardShortcutOfficeSlug ? 'bg-blue-700' : 'hover:bg-blue-700' }} text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">
                                    Dashboard
                                </a>
                            @endif
                            <details class="relative">
                                <summary class="lgu-btn list-none cursor-pointer px-4 py-2.5 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800 [&::-webkit-details-marker]:hidden" aria-label="Open account menu">
                                    <span class="inline-flex items-center gap-1.5">
                                        Account
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                        </svg>
                                    </span>
                                </summary>
                                <div class="absolute right-0 z-30 mt-2 w-44 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl">
                                    @php($accountUser = auth()->user())
                                    <div class="border-b border-slate-100 px-4 py-2.5">
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
                                            <div class="min-w-0">
                                                <p class="text-[11px] text-slate-500">Signed in as</p>
                                                <p class="truncate text-sm font-medium text-slate-700">{{ $accountUser->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('profile') }}" class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Profile</a>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">Logout</button>
                                    </form>
                                </div>
                            </details>
                        @else
                            <a href="{{ route('login') }}" class="lgu-btn px-4 py-2.5 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">Log in</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
    @endif
    <main id="main-content" class="{{ $hideNav ? 'h-dvh overflow-hidden p-0' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8' }}" role="main">
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
