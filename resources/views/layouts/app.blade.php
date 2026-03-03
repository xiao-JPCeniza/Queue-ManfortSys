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
    <nav class="bg-blue-800 text-white shadow-md" role="navigation" aria-label="Main">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-4 flex-wrap">
                    <a href="{{ url('/') }}" class="text-xl font-bold text-white hover:text-blue-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800 rounded px-1">LGU Queue System</a>
                    @auth
                        <span class="text-blue-200 text-sm py-2">{{ auth()->user()->name }} <span class="text-blue-300">({{ auth()->user()->role?->name }})</span></span>
                    @endauth
                </div>
                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="lgu-btn px-4 py-2.5 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">Dashboard</a>
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('super-admin.offices') }}" class="lgu-btn px-4 py-2.5 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">Queueing Offices</a>
                        @endif
                        @if(auth()->user()->isQueueMaster() || auth()->user()->isSuperAdmin())
                            <a href="{{ route('queue-master.index') }}" class="lgu-btn px-4 py-2.5 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">Queue Master</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="lgu-btn px-4 py-2.5 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="lgu-btn px-4 py-2.5 rounded-lg hover:bg-blue-700 text-white font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">Log in</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" role="main">
        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl text-sm" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl text-sm" role="alert">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
    @livewireScripts
</body>
</html>
