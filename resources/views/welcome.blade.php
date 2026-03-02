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
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="bg-emerald-800 text-white shadow-lg">
            <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">LGU Queue System</h1>
                    <p class="text-emerald-200 text-sm">Municipality of Manolo Fortich</p>
                </div>
                <a href="{{ route('login') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg font-medium text-sm transition">
                    Staff login
                </a>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center p-6">
            <div class="text-center max-w-2xl">
                <h2 class="text-3xl font-bold text-slate-800 mb-4">Welcome to the LGU Queue System</h2>
                <p class="text-slate-600 mb-8">
                    Get a queue number by selecting your office (MENRO, MISO, MAO, etc.). Your ticket will be announced by voice and shown in a pop-up.
                    Staff may log in to manage queues and serve clients.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('queue.client') }}" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition shadow-md">
                        Get queue number (Client)
                    </a>
                    <a href="{{ route('login') }}" class="px-6 py-3 bg-slate-600 hover:bg-slate-700 text-white font-medium rounded-xl transition shadow-md">
                        Staff / Admin login
                    </a>
                </div>
            </div>
        </main>

        <footer class="py-4 text-center text-slate-500 text-sm border-t border-slate-200">
            Municipality of Manolo Fortich &copy; {{ date('Y') }} — Queue Management System
        </footer>
    </div>
</body>
</html>
