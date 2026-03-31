<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased">
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 flex flex-col flex-shrink-0" style="background: linear-gradient(180deg, #002E52 0%, #0056B3 100%);">
        <div class="h-16 flex items-center px-5 border-b border-white/10">
            <img src="{{ asset('images/altumcredo_logo.png') }}" alt="Altum Credo" class="h-9 bg-white rounded-lg p-1 object-contain">
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                Dashboard
            </a>
            <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('tickets.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Tickets
            </a>
            <a href="{{ route('tickets.create') }}" class="flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Ticket
            </a>
            @if(auth()->user()->isResolver())
            <a href="{{ route('team.index') }}" class="flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('team.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                Team
            </a>
            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('reports.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Reports
            </a>
            <div class="mt-4 px-6 py-1 text-xs text-gold-400/60 uppercase tracking-wider font-semibold">Admin</div>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                Users
            </a>
            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('admin.categories.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categories
            </a>
            @endif
        </nav>
        <div class="border-t border-white/10 p-4">
            <div class="text-xs text-gray-300 font-medium">{{ auth()->user()->name }}</div>
            <div class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="text-xs text-red-400 hover:text-red-300">Logout</button>
            </form>
        </div>
        <div class="px-4 pb-3">
            <div class="text-[10px] text-gray-500 text-center">Developed by <span class="text-gold-400/70 font-semibold">5P Media</span></div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b flex items-center justify-between px-6 flex-shrink-0">
            <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('notifications.index') }}" class="relative text-gray-500 hover:text-brand-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    @if($unread > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">{{ $unread }}</span>
                    @endif
                </a>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-6 pt-4">
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
        </div>

        <!-- Page content -->
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
