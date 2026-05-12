@php
    $tabs = [
        ['developer.dialer.home',            'Dialer',      'developer.dialer.home'],
        ['developer.dialer.customers.index', 'Customers',   'developer.dialer.customers.*'],
        ['developer.dialer.tickets.index',   'Call log',    'developer.dialer.tickets.*'],
    ];
@endphp
<div class="flex items-center gap-1 border-b border-gray-200 mb-6 text-sm overflow-x-auto">
    @foreach($tabs as [$route, $label, $active])
    <a href="{{ route($route) }}"
       class="px-4 py-2 -mb-px border-b-2 whitespace-nowrap {{ request()->routeIs($active) ? 'border-brand-500 text-brand-700 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
        {{ $label }}
    </a>
    @endforeach
    <a href="{{ route('developer.dialer.tickets.index', ['status' => 'missed']) }}"
       class="px-4 py-2 -mb-px border-b-2 whitespace-nowrap {{ request()->routeIs('developer.dialer.tickets.*') && request('status') === 'missed' ? 'border-amber-500 text-amber-700 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
        Missed calls
    </a>
</div>
