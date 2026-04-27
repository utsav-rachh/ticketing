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
    <style>
        /* Collapsible sidebar */
        aside.sidebar { transition: width 180ms ease; }
        aside.sidebar[data-collapsed="true"]  { width: 5.25rem; }
        aside.sidebar[data-collapsed="false"] { width: 16rem; }
        /* Collapsed: stack icon + label vertically so names stay visible */
        aside.sidebar[data-collapsed="true"] .sidebar-link  {
            flex-direction: column; gap: 2px;
            justify-content: center; align-items: center;
            padding: 0.55rem 0.25rem;
        }
        aside.sidebar[data-collapsed="true"] .sidebar-label {
            font-size: 10px; line-height: 1.1; text-align: center;
            white-space: normal; word-break: break-word;
        }
        aside.sidebar[data-collapsed="true"] .logo-wrap     { justify-content: center; padding-left: 0; padding-right: 0; }
        aside.sidebar[data-collapsed="true"] .logo-text     { display: none; }
        aside.sidebar[data-collapsed="true"] .section-label { font-size: 9px; padding-left: 0; padding-right: 0; text-align: center; }
        aside.sidebar[data-collapsed="true"] .footer-block  { padding: 0.5rem 0.25rem; }
        aside.sidebar[data-collapsed="true"] .footer-detail { display: none; }
        /* Hide scrollbar on sidebar nav (still scrollable via wheel/drag) */
        aside.sidebar nav { scrollbar-width: none; -ms-overflow-style: none; }
        aside.sidebar nav::-webkit-scrollbar { width: 0; height: 0; display: none; }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
@php
    $user = auth()->user();
    $companyName = config('app.company_name', 'Altum Credo Finance Private Limited');
@endphp
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar (expanded by default; state persisted in localStorage) -->
    <aside class="sidebar flex flex-col flex-shrink-0" data-collapsed="false" id="appSidebar"
           style="background: linear-gradient(180deg, #002E52 0%, #0056B3 100%);">

        <div class="logo-wrap h-20 flex items-center gap-3 px-3 bg-white border-b border-white/10">
            <img src="{{ asset('images/altumcredo_logo.png') }}" alt="Altum Credo" class="h-12 object-contain">
            <div class="logo-text leading-tight">
                <div class="text-[13px] font-bold" style="color:#002E52;">Altum Credo</div>
                <div class="text-[10px] text-gray-500">Home Finance</div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-3">
            @php
                $navItems = [
                    ['dashboard',       'Dashboard',   '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>', 'dashboard'],
                    ['tickets.index',   'Tickets',     '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>', 'tickets.*'],
                    ['tickets.create',  'New Ticket',  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>', 'tickets.create'],
                ];
            @endphp
            @foreach($navItems as [$route,$label,$svg,$active])
            <a href="{{ route($route) }}" title="{{ $label }}"
               class="sidebar-link flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs($active) ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $svg !!}</svg>
                <span class="sidebar-label">{{ $label }}</span>
            </a>
            @endforeach

            @if($user->isResolver() || $user->isAdmin())
            <div class="section-label mt-4 px-6 py-1 text-xs text-gold-400/60 uppercase tracking-wider font-semibold">Work</div>
            <a href="{{ route('team.index') }}" title="Team"
               class="sidebar-link flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('team.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                <span class="sidebar-label">Team</span>
            </a>
            <a href="{{ route('reports.index') }}" title="Reports"
               class="sidebar-link flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('reports.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="sidebar-label">Reports</span>
            </a>
            @if($user->canApproveExpenses())
            <a href="{{ route('expenses.approvals') }}" title="Expense Approvals"
               class="sidebar-link flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('expenses.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="sidebar-label">Expense Approvals</span>
            </a>
            @endif
            @endif

            @if($user->isAdmin())
            <div class="section-label mt-4 px-6 py-1 text-xs text-gold-400/60 uppercase tracking-wider font-semibold">Admin</div>
            @foreach([
                ['admin.users.index',        'Users',         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>', 'admin.users.*'],
                ['admin.regions.index',      'States',        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a9 9 0 019 9c0 6.075-9 13-9 13S3 17.075 3 11a9 9 0 019-9zm0 4a5 5 0 100 10 5 5 0 000-10z"/>',            'admin.regions.*'],
                ['admin.branches.index',     'Branches',      '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21V10m0 0l9-6 9 6M3 10h18M9 21v-6a2 2 0 012-2h2a2 2 0 012 2v6"/>',                    'admin.branches.*'],
                ['admin.vendors.index',      'Vendors',       '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>', 'admin.vendors.*'],
                ['admin.categories.index',   'Categories',    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>', 'admin.categories.*'],
                ['admin.subcategories.index','Issue Types',   '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>',                                                          'admin.subcategories.*'],
                ['admin.tat.index',          'TAT Config',    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                     'admin.tat.*'],
                ['admin.audit-logs.index',   'Audit Logs',    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'admin.audit-logs.*'],
            ] as [$r,$l,$svg,$active])
            <a href="{{ route($r) }}" title="{{ $l }}"
               class="sidebar-link flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs($active) ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $svg !!}</svg>
                <span class="sidebar-label">{{ $l }}</span>
            </a>
            @endforeach
            @endif
        </nav>

        <!-- Footer: user, company, attribution, hamburger -->
        <div class="sidebar-footer border-t border-white/10 footer-block p-4">
            <div class="footer-detail">
                <div class="text-xs text-gray-300 font-medium truncate">{{ $user->name }}</div>
                <div class="text-[11px] text-gray-400">
                    {{ ucfirst($user->role) }}{{ $user->resolver_level ? ' · '.strtoupper($user->resolver_level) : '' }}
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2 footer-detail">
                @csrf
                <button type="submit" class="text-xs text-red-400 hover:text-red-300">Logout</button>
            </form>
            <div class="mt-3 pt-3 border-t border-white/10 text-[10px] leading-snug footer-detail">
                <div class="text-gray-400">{{ $companyName }}</div>
                <div class="mt-1">
                    <span class="text-gray-400">Developed by</span>
                    <span class="font-bold tracking-wide" style="color:#38BDF8;">Cybermedia</span>
                </div>
            </div>
        </div>

        <!-- Collapse toggle (always visible, pinned at bottom) -->
        <button type="button" id="sidebarToggle"
                class="w-full py-3 border-t border-white/10 text-gray-300 hover:bg-white/10 flex items-center justify-center"
                title="Toggle sidebar">
            <svg id="sidebarToggleIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white border-b flex items-center justify-between px-6 flex-shrink-0">
            <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('notifications.index') }}" class="relative text-gray-500 hover:text-brand-500" id="notifBell">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @php $unread = $user->unreadNotifications()->count(); @endphp
                    <span id="notifCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center {{ $unread > 0 ? '' : 'hidden' }}">{{ $unread }}</span>
                </a>
            </div>
        </header>

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

        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
(function() {
    const sidebar = document.getElementById('appSidebar');
    const toggle  = document.getElementById('sidebarToggle');
    const stored  = localStorage.getItem('ticketing.sidebar.collapsed');
    if (stored !== null) sidebar.dataset.collapsed = stored;
    toggle.addEventListener('click', () => {
        const now = sidebar.dataset.collapsed === 'true' ? 'false' : 'true';
        sidebar.dataset.collapsed = now;
        localStorage.setItem('ticketing.sidebar.collapsed', now);
    });

    // Unread notification polling
    const badge = document.getElementById('notifCount');
    async function refreshCount() {
        try {
            const res = await fetch('{{ route("notifications.unreadCount") }}', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (data.count > 0) { badge.textContent = data.count; badge.classList.remove('hidden'); }
            else { badge.classList.add('hidden'); }
        } catch (e) { /* ignore */ }
    }
    setInterval(refreshCount, 60000);
})();
</script>
@livewireScripts
</body>
</html>
