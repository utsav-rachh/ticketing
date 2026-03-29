@extends('layouts.app')
@section('title', 'Team Overview')
@section('content')
<h2 class="text-xl font-bold text-gray-700 mb-4">Team Overview</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @forelse($teamStats as $stat)
    <div class="bg-white rounded-lg shadow p-5">
        <div class="font-semibold text-gray-800">{{ $stat['user']->name }}</div>
        <div class="text-xs text-gray-500 mb-3">{{ ucfirst(str_replace('_',' ',$stat['user']->role)) }}</div>
        <div class="grid grid-cols-3 gap-2 text-center text-sm">
            <div><div class="font-bold text-brand-500">{{ $stat['open'] }}</div><div class="text-gray-400 text-xs">Open</div></div>
            <div><div class="font-bold text-green-600">{{ $stat['resolved'] }}</div><div class="text-gray-400 text-xs">Resolved</div></div>
            <div><div class="font-bold text-red-600">{{ $stat['violated'] }}</div><div class="text-gray-400 text-xs">TAT Violated</div></div>
        </div>
        <a href="{{ route('team.member', $stat['user']) }}" class="mt-3 block text-center text-xs text-brand-500 hover:underline">View Tickets &rarr;</a>
    </div>
    @empty
    <div class="col-span-3 text-center text-gray-400 py-8">No team members found.</div>
    @endforelse
</div>
@endsection
