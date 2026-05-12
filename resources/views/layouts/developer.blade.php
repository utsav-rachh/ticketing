<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#002E52">
    <title>{{ config('app.name') }} — Developer · @yield('title', 'Workspace')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('images/altumcredo_logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dev-card { transition: transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease; }
        .dev-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,46,82,0.12); }
        @media (max-width: 767.98px) {
            .overflow-x-auto > table { min-width: 720px; }
        }
        @supports (padding: env(safe-area-inset-bottom)) { body { padding-bottom: env(safe-area-inset-bottom); } }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">
@php $user = auth()->user(); @endphp
<div class="min-h-screen flex flex-col">

    <header class="bg-white border-b border-gray-200 border-t-2 border-t-brand-500">
        <div class="max-w-7xl mx-auto px-4 md:px-6 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <img src="{{ asset('images/altumcredo_logo.png') }}" alt="Altum Credo" class="h-9 md:h-11 object-contain flex-shrink-0">
                <a href="{{ route('developer.home') }}" class="hidden sm:block min-w-0 border-l border-gray-200 pl-3 leading-tight">
                    <span class="text-sm md:text-base font-bold text-gray-800 block truncate">Developer Workspace</span>
                    <span class="text-[11px] text-gray-400 block truncate">CTS · ATS · Dialer — internal launcher</span>
                </a>
            </div>
            <nav class="flex items-center gap-1 text-sm">
                @php
                    $devTabs = [
                        ['developer.home',        'developer.home',        route('developer.home'),        'Apps'],
                        [null,                    'dashboard',             route('dashboard'),             'CTS'],
                        ['developer.assets',      'developer.assets',      route('developer.assets'),      'ATS'],
                        ['developer.dialer.*',    'developer.dialer.*',    route('developer.dialer.home'), 'Dialer'],
                    ];
                @endphp
                @foreach($devTabs as [$pattern, $matchOn, $href, $label])
                @php $isActive = $pattern && request()->routeIs($pattern); @endphp
                <a href="{{ $href }}"
                   class="px-3 py-1.5 rounded-md whitespace-nowrap {{ $isActive ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }}">{{ $label }}</a>
                @endforeach
                <span class="hidden md:inline text-gray-300 mx-1">|</span>
                <span class="hidden md:inline text-xs text-gray-500">{{ $user->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ml-1 text-xs px-3 py-1.5 rounded-md text-red-600 hover:bg-red-50">Logout</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 md:px-6 py-6">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded mb-4 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-2 rounded mb-4 text-sm">{{ session('error') }}</div>
        @endif
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-2 rounded mb-4 text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white text-center text-xs text-gray-400 py-3 px-4">
        ATS &amp; Dialer are developer-only — not visible to admin / CISO / management.
        <span class="mx-1.5 text-gray-300">·</span>
        Developed by <span class="font-bold tracking-wide" style="color:#38BDF8;">5P Media</span>
    </footer>
</div>
</body>
</html>
