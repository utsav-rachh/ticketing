<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#002E52">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Ticketing') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/altumcredo_logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/altumcredo_logo.png') }}">
    <title>{{ config('app.name') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* ---------- Desktop sidebar (collapsible) ---------- */
        aside.sidebar { transition: width 180ms ease, transform 220ms ease; }
        aside.sidebar[data-collapsed="true"]  { width: 5.25rem; }
        aside.sidebar[data-collapsed="false"] { width: 16rem; }
        aside.sidebar[data-collapsed="true"] .sidebar-link {
            flex-direction: column; gap: 2px;
            justify-content: center; align-items: center;
            padding: 0.55rem 0.25rem;
        }
        aside.sidebar[data-collapsed="true"] .sidebar-label {
            font-size: 10px; line-height: 1.1; text-align: center;
            white-space: normal; word-break: break-word;
        }
        aside.sidebar[data-collapsed="true"] .logo-wrap     { padding-left: 0; padding-right: 0; }
        aside.sidebar[data-collapsed="true"] .section-label { font-size: 9px; padding-left: 0; padding-right: 0; text-align: center; }
        aside.sidebar[data-collapsed="true"] .footer-block  { padding: 0.5rem 0.25rem; }
        aside.sidebar[data-collapsed="true"] .footer-detail { display: none; }
        aside.sidebar nav { scrollbar-width: none; -ms-overflow-style: none; }
        aside.sidebar nav::-webkit-scrollbar { width: 0; height: 0; display: none; }

        /* ---------- Mobile sidebar drawer ---------- */
        @media (max-width: 1023.98px) {
            aside.sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 50;
                width: 17rem !important;          /* override desktop collapsed/expanded */
                transform: translateX(-100%);
                box-shadow: 0 12px 32px rgba(0,0,0,0.25);
            }
            aside.sidebar[data-mobile-open="true"] { transform: translateX(0); }
            /* On mobile the sidebar is always "expanded" visually so labels show */
            aside.sidebar .sidebar-link { flex-direction: row !important; gap: 0.75rem !important; padding: 0.75rem 1.25rem !important; }
            aside.sidebar .sidebar-label { font-size: 0.875rem !important; text-align: left !important; }
            aside.sidebar .footer-detail, aside.sidebar .section-label { display: block !important; }
            #sidebarToggle { display: none; }
            #sidebarBackdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 40; }
            #sidebarBackdrop.is-open { display: block; }
            body.no-scroll { overflow: hidden; }
        }

        /* ---------- Page chrome scaling on small screens ---------- */
        .app-header { height: 3.5rem; padding-left: 0.75rem; padding-right: 0.75rem; }
        @media (min-width: 768px) { .app-header { height: 4rem; padding-left: 1.5rem; padding-right: 1.5rem; } }

        /* Make tables breathe on mobile: edge-to-edge horizontal scroll. */
        @media (max-width: 767.98px) {
            main.app-main { padding: 0.75rem !important; }
            .px-6.pt-4 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
            /* Tables inside cards keep their card chrome but can scroll horizontally */
            .overflow-x-auto > table { min-width: 720px; }
        }

        /* Respect device safe areas (iOS notch / home indicator) */
        @supports (padding: env(safe-area-inset-bottom)) {
            body { padding-bottom: env(safe-area-inset-bottom); }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
@php
    $user = auth()->user();
    $companyName = config('app.company_name', 'Altum Credo Finance Private Limited');
@endphp
<div class="flex h-screen overflow-hidden">

    <!-- Mobile backdrop -->
    <div id="sidebarBackdrop"></div>

    <!-- Sidebar (desktop: pinned + collapsible; mobile: slide-in drawer) -->
    <aside class="sidebar flex flex-col flex-shrink-0" data-collapsed="false" data-mobile-open="false" id="appSidebar"
           style="background: linear-gradient(180deg, #002E52 0%, #0056B3 100%);">

        <div class="logo-wrap h-16 md:h-20 flex items-center justify-between px-3 bg-white border-b border-gray-200">
            <img src="{{ asset('images/altumcredo_logo.png') }}" alt="Altum Credo" class="h-10 md:h-12 object-contain">
            <button type="button" id="sidebarCloseMobile"
                    class="lg:hidden p-2 -mr-1 text-gray-500 hover:text-gray-800" aria-label="Close menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
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

            @php
                $canSeeTeamReports = $user->isResolver() || $user->isAdmin();
                $showWorkSection   = $canSeeTeamReports || $user->canManageProjects() || $user->canApproveExpenses();
            @endphp
            @if($showWorkSection)
            <div class="section-label mt-4 px-6 py-1 text-xs text-gold-400/60 uppercase tracking-wider font-semibold">Work</div>
            @if($user->canManageProjects())
            <a href="{{ route('projects.index') }}" title="Projects"
               class="sidebar-link flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('projects.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                <span class="sidebar-label">Projects</span>
            </a>
            @endif
            @if($canSeeTeamReports)
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
            @endif
            @if($user->canApproveExpenses())
            <a href="{{ route('expenses.approvals') }}" title="Expense Approvals"
               class="sidebar-link flex items-center gap-3 px-6 py-2.5 text-sm hover:bg-white/10 {{ request()->routeIs('expenses.*') ? 'bg-white/10 text-white border-r-2 border-gold-400' : 'text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="sidebar-label">Expense Approvals</span>
            </a>
            @endif
            @endif

            @if($user->canManageAdmin())
            <div class="section-label mt-4 px-6 py-1 text-xs text-gold-400/60 uppercase tracking-wider font-semibold">Admin</div>
            @foreach([
                ['admin.users.index',        'Users',         '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>', 'admin.users.*'],
                ['admin.regions.index',      'States',        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a9 9 0 019 9c0 6.075-9 13-9 13S3 17.075 3 11a9 9 0 019-9zm0 4a5 5 0 100 10 5 5 0 000-10z"/>',            'admin.regions.*'],
                ['admin.branches.index',     'Branches',      '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21V10m0 0l9-6 9 6M3 10h18M9 21v-6a2 2 0 012-2h2a2 2 0 012 2v6"/>',                    'admin.branches.*'],
                ['admin.vendors.index',      'Vendors',       '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>', 'admin.vendors.*'],
                ['admin.categories.index',   'Categories',    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>', 'admin.categories.*'],
                ['admin.subcategories.index','Issue Types',   '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>',                                                          'admin.subcategories.*'],
                ['admin.tat.index',          'TAT Config',    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',                                     'admin.tat.*'],
                ['admin.working-hours.index','Working Hours', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>', 'admin.working-hours.*'],
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

        <!-- Footer: user, company, attribution -->
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
                    <span class="font-bold tracking-wide" style="color:#38BDF8;">5P Media</span>
                </div>
            </div>
        </div>

        <!-- Desktop collapse toggle (hidden on mobile via CSS) -->
        <button type="button" id="sidebarToggle"
                class="w-full py-3 border-t border-white/10 text-gray-300 hover:bg-white/10 flex items-center justify-center"
                title="Toggle sidebar">
            <svg id="sidebarToggleIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        <header class="app-header bg-white border-b border-gray-200 border-t-2 border-t-brand-500 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <button type="button" id="sidebarOpenMobile"
                        class="lg:hidden p-2 -ml-2 text-gray-700 hover:text-brand-600" aria-label="Open menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-base md:text-lg font-semibold text-gray-800 truncate">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3 md:gap-4">
                <a href="{{ route('notifications.index') }}" class="relative text-gray-500 hover:text-brand-500 p-1" id="notifBell" aria-label="Notifications">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @php $unread = $user->unreadNotifications()->count(); @endphp
                    <span id="notifCount" class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] rounded-full min-w-[16px] h-4 px-1 flex items-center justify-center {{ $unread > 0 ? '' : 'hidden' }}">{{ $unread }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-red-600 px-2 py-1" aria-label="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <div class="px-3 md:px-6 pt-3 md:pt-4">
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

        <main class="app-main flex-1 overflow-y-auto p-3 md:p-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
(function() {
    const sidebar  = document.getElementById('appSidebar');
    const toggle   = document.getElementById('sidebarToggle');
    const openBtn  = document.getElementById('sidebarOpenMobile');
    const closeBtn = document.getElementById('sidebarCloseMobile');
    const backdrop = document.getElementById('sidebarBackdrop');

    // Desktop persisted collapse state
    const stored  = localStorage.getItem('ticketing.sidebar.collapsed');
    if (stored !== null) sidebar.dataset.collapsed = stored;
    toggle.addEventListener('click', () => {
        const now = sidebar.dataset.collapsed === 'true' ? 'false' : 'true';
        sidebar.dataset.collapsed = now;
        localStorage.setItem('ticketing.sidebar.collapsed', now);
    });

    // Mobile drawer
    const isMobile = () => window.matchMedia('(max-width: 1023.98px)').matches;
    const openDrawer = () => {
        sidebar.dataset.mobileOpen = 'true';
        backdrop.classList.add('is-open');
        document.body.classList.add('no-scroll');
    };
    const closeDrawer = () => {
        sidebar.dataset.mobileOpen = 'false';
        backdrop.classList.remove('is-open');
        document.body.classList.remove('no-scroll');
    };
    if (openBtn)  openBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);
    // Auto-close drawer on nav click (mobile only)
    sidebar.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => { if (isMobile()) closeDrawer(); });
    });
    // Close drawer if user resizes to desktop
    window.addEventListener('resize', () => { if (!isMobile()) closeDrawer(); });
    // ESC closes drawer
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && isMobile()) closeDrawer(); });

    // Auto-label cells for mobile card tables: any <table data-mobile="cards">
    // gets data-label populated from its <thead> th text. Cells whose label
    // is empty (e.g. action columns) get data-label-skip so the ::before is hidden.
    function labelCardTables() {
        document.querySelectorAll('table[data-mobile="cards"]').forEach(tbl => {
            if (tbl.dataset.labeled === '1') return;
            const heads = Array.from(tbl.querySelectorAll('thead th')).map(th => th.textContent.trim());
            tbl.querySelectorAll('tbody tr').forEach(tr => {
                Array.from(tr.children).forEach((td, i) => {
                    if (td.tagName !== 'TD') return;
                    if (td.hasAttribute('data-label')) return;
                    const colspan = parseInt(td.getAttribute('colspan') || '1', 10);
                    if (colspan > 1) return; // empty-state rows
                    const label = heads[i] || '';
                    if (label) td.setAttribute('data-label', label);
                });
            });
            tbl.dataset.labeled = '1';
        });
    }
    labelCardTables();
    // Re-run if Livewire or other code re-renders content
    document.addEventListener('livewire:navigated', labelCardTables);

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
