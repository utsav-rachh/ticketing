@php
    $tabs = [
        ['developer.dialer.home',            'Dialer',      'developer.dialer.home'],
        ['developer.dialer.customers.index', 'Customers',   'developer.dialer.customers.*'],
        ['developer.dialer.tickets.index',   'Call log',    'developer.dialer.tickets.*'],
    ];
@endphp
<div class="flex items-center gap-1 border-b border-slate-700 mb-6 text-sm overflow-x-auto">
    @foreach($tabs as [$route, $label, $active])
    <a href="{{ route($route) }}"
       class="px-4 py-2 -mb-px border-b-2 whitespace-nowrap {{ request()->routeIs($active) ? 'border-emerald-400 text-white' : 'border-transparent text-slate-400 hover:text-slate-200' }}">
        {{ $label }}
    </a>
    @endforeach
    <a href="{{ route('developer.dialer.tickets.index', ['status' => 'missed']) }}"
       class="px-4 py-2 -mb-px border-b-2 whitespace-nowrap {{ request()->routeIs('developer.dialer.tickets.*') && request('status') === 'missed' ? 'border-amber-400 text-white' : 'border-transparent text-slate-400 hover:text-slate-200' }}">
        Missed calls
    </a>
</div>
