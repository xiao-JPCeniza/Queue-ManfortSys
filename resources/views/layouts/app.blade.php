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
    <nav class="bg-emerald-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="text-xl font-bold">LGU Queue System</a>
                    @auth
                        <span class="text-emerald-200 text-sm">{{ auth()->user()->name }} ({{ auth()->user()->role?->name }})</span>
                    @endauth
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-md hover:bg-emerald-700">Dashboard</a>
                        @if(auth()->user()->isQueueMaster() || auth()->user()->isSuperAdmin())
                            <a href="{{ route('queue-master.index') }}" class="px-3 py-1.5 rounded-md hover:bg-emerald-700">Queue Master</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-md hover:bg-emerald-700">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-md hover:bg-emerald-700">Log in</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-800 rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
    @livewireScripts
</body>
</html>
