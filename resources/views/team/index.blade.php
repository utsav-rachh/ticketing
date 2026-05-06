@extends('layouts.app')
@section('title', 'Team Overview')
@section('content')

@php
    $statBlock = function ($stats) {
        $stats = $stats ?? ['open' => 0, 'resolved' => 0, 'violated' => 0, 'total' => 0];
        return $stats;
    };
@endphp

{{-- IT Head card (sits above the support-type groups, full-width) --}}
@if($itHead)
@php $s = $statBlock($statsById[$itHead->id] ?? null); @endphp
<div class="bg-white rounded-lg shadow border-l-4 border-brand-500 p-4 md:p-5 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <div class="text-[10px] uppercase tracking-wider text-brand-500 font-semibold">IT Head</div>
        <div class="font-semibold text-gray-800 text-base md:text-lg">{{ $itHead->name }}</div>
        <div class="text-xs text-gray-500 break-all">{{ $itHead->email }} · {{ $itHead->employee_id ?? '—' }}</div>
    </div>
    <div class="grid grid-cols-4 gap-2 md:gap-4 text-center text-sm">
        <div><div class="font-bold text-gray-700">{{ $s['total'] }}</div><div class="text-[10px] text-gray-400 uppercase">Total</div></div>
        <div><div class="font-bold text-brand-500">{{ $s['open'] }}</div><div class="text-[10px] text-gray-400 uppercase">Open</div></div>
        <div><div class="font-bold text-green-600">{{ $s['resolved'] }}</div><div class="text-[10px] text-gray-400 uppercase">Resolved</div></div>
        <div><div class="font-bold {{ $s['violated'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $s['violated'] }}</div><div class="text-[10px] text-gray-400 uppercase">Violated</div></div>
    </div>
</div>
@endif

{{-- Support-type groups: TL on top, juniors stacked below --}}
@forelse($groups as $group)
<div class="mb-8">
    <div class="flex items-center gap-2 mb-3">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">{{ $group['label'] }} Team</h3>
        <span class="h-px flex-1 bg-gray-200"></span>
    </div>

    {{-- TL card (lead of this support type) --}}
    @if($group['tl'])
    @php $s = $statBlock($statsById[$group['tl']->id] ?? null); @endphp
    <div class="bg-white rounded-lg shadow p-4 md:p-5 mb-4 border-l-4 {{ $group['type'] === 'application' ? 'border-blue-500' : 'border-amber-500' }}">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <div class="text-[10px] uppercase tracking-wider {{ $group['type'] === 'application' ? 'text-blue-500' : 'text-amber-600' }} font-semibold">
                    Team Lead — {{ ucfirst($group['type']) }}
                </div>
                <div class="font-semibold text-gray-800 text-base md:text-lg">{{ $group['tl']->name }}</div>
                <div class="text-xs text-gray-500 break-all">{{ $group['tl']->email }} · {{ $group['tl']->employee_id ?? '—' }}</div>
            </div>
            <div class="grid grid-cols-4 gap-2 md:gap-4 text-center text-sm">
                <div><div class="font-bold text-gray-700">{{ $s['total'] }}</div><div class="text-[10px] text-gray-400 uppercase">Total</div></div>
                <div><div class="font-bold text-brand-500">{{ $s['open'] }}</div><div class="text-[10px] text-gray-400 uppercase">Open</div></div>
                <div><div class="font-bold text-green-600">{{ $s['resolved'] }}</div><div class="text-[10px] text-gray-400 uppercase">Resolved</div></div>
                <div><div class="font-bold {{ $s['violated'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $s['violated'] }}</div><div class="text-[10px] text-gray-400 uppercase">Violated</div></div>
            </div>
            <div>
                <a href="{{ route('team.member', $group['tl']) }}" class="text-xs text-brand-500 hover:underline whitespace-nowrap">View tickets &rarr;</a>
            </div>
        </div>
    </div>
    @endif

    {{-- Juniors grid --}}
    @if($group['juniors']->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 ml-0 md:ml-6">
        @foreach($group['juniors'] as $jr)
        @php $s = $statBlock($statsById[$jr->id] ?? null); @endphp
        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between gap-2 mb-2">
                <div>
                    <div class="font-semibold text-gray-800">{{ $jr->name }}</div>
                    <div class="text-[11px] text-gray-500">Junior · {{ $jr->employee_id ?? '—' }}</div>
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 capitalize">{{ $jr->assigned_support_type }}</span>
            </div>
            <div class="grid grid-cols-4 gap-2 text-center text-xs mt-3">
                <div><div class="font-bold text-gray-700">{{ $s['total'] }}</div><div class="text-[9px] text-gray-400 uppercase">Total</div></div>
                <div><div class="font-bold text-brand-500">{{ $s['open'] }}</div><div class="text-[9px] text-gray-400 uppercase">Open</div></div>
                <div><div class="font-bold text-green-600">{{ $s['resolved'] }}</div><div class="text-[9px] text-gray-400 uppercase">Resolved</div></div>
                <div><div class="font-bold {{ $s['violated'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $s['violated'] }}</div><div class="text-[9px] text-gray-400 uppercase">Violated</div></div>
            </div>
            <a href="{{ route('team.member', $jr) }}" class="mt-3 block text-center text-xs text-brand-500 hover:underline">View tickets &rarr;</a>
        </div>
        @endforeach
    </div>
    @else
    <div class="ml-6 text-xs text-gray-400">No juniors assigned to this team yet.</div>
    @endif
</div>
@empty
<div class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
    No team data available for your role.
</div>
@endforelse
@endsection
