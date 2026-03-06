<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|source-serif-4:500,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, sans-serif;
        }

        .gov-font-heading {
            font-family: 'Source Serif 4', Georgia, 'Times New Roman', serif;
            letter-spacing: -0.015em;
        }
    </style>
</head>
<body class="relative min-h-screen overflow-x-hidden bg-slate-100 text-slate-900 antialiased flex items-center justify-center px-4 py-8 md:py-12">
    @hasSection('content')
        @yield('content')
    @else
        {{ $slot ?? '' }}
    @endif
    @livewireScripts
</body>
</html>
