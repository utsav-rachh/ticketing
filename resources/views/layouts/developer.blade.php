<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Developer · @yield('title', 'Sandbox')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0f172a; }
        .dev-card { transition: transform 150ms ease, box-shadow 150ms ease; }
        .dev-card:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,0,0,0.35); }
    </style>
</head>
<body class="font-sans antialiased text-slate-200">
@php $user = auth()->user(); @endphp
<div class="min-h-screen flex flex-col">
    <header class="bg-slate-900/90 border-b border-slate-700">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-lg flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold">DEV</div>
                <div class="min-w-0">
                    <a href="{{ route('developer.home') }}" class="text-base md:text-lg font-bold text-white truncate block">Developer Sandbox</a>
                    <div class="text-[11px] text-slate-400 truncate">Internal — features under build, not yet released</div>
                </div>
            </div>
            <nav class="flex items-center gap-2 text-sm">
                <a href="{{ route('developer.home') }}"
                   class="px-3 py-1.5 rounded {{ request()->routeIs('developer.home') ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white' }}">Home</a>
                <a href="{{ route('developer.assets') }}"
                   class="px-3 py-1.5 rounded {{ request()->routeIs('developer.assets') ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white' }}">Asset Mgmt</a>
                <a href="{{ route('developer.dialer') }}"
                   class="px-3 py-1.5 rounded {{ request()->routeIs('developer.dialer') ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white' }}">Dialer</a>
                <span class="hidden md:inline text-xs text-slate-500 mx-2">·</span>
                <span class="hidden md:inline text-xs text-slate-400">{{ $user->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ml-2 text-xs px-3 py-1.5 rounded bg-red-500/20 text-red-300 hover:bg-red-500/30">Logout</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-6xl w-full mx-auto px-6 py-8">
        @if(session('success'))
        <div class="mb-4 px-4 py-2 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-slate-800 text-center text-xs text-slate-500 py-4">
        Sandbox build — not visible to admin / IT / management. Promote a feature to the main app only after it passes review.
    </footer>
</div>
</body>
</html>
