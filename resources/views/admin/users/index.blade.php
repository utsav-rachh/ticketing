@extends('layouts.app')
@section('title', 'Manage Users')
@section('content')
<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <h2 class="text-lg md:text-xl font-bold text-gray-700">Users</h2>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.users.export') }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded text-sm font-medium btn-touch hover:bg-gray-50 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download Excel
        </a>
        <a href="{{ route('admin.users.create') }}" class="text-white px-4 py-2 rounded text-sm font-medium btn-touch" style="background:#0056B3;">+ New user</a>
    </div>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm" data-mobile="cards">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Role</th>
                <th class="px-4 py-3 text-left">Level</th>
                <th class="px-4 py-3 text-left">Branch / State</th>
                <th class="px-4 py-3 text-left">Auto-route</th>
                <th class="px-4 py-3 text-left">Flags</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50 {{ $user->deleted_at ? 'opacity-50' : '' }}">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                <td class="px-4 py-3 capitalize text-xs"><span class="px-2 py-0.5 bg-brand-100 text-brand-700 rounded-full">{{ $user->role }}</span></td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $user->resolver_level ? strtoupper($user->resolver_level) : '—' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $user->branch->name ?? '—' }} / {{ $user->region->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    @php
                        $regionLabel = $user->assignedRegions->isNotEmpty()
                            ? $user->assignedRegions->pluck('name')->join(', ')
                            : ($user->assignedRegion->name ?? 'any');
                    @endphp
                    @if($user->assigned_support_type || $user->assignedRegions->isNotEmpty() || $user->assigned_region_id)
                        {{ $user->assigned_support_type ?: 'any' }} · {{ $regionLabel }}
                    @else — @endif
                </td>
                <td class="px-4 py-3 text-xs">
                    @if($user->isManagement())<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full">MGMT</span>@endif
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $user->is_active && !$user->deleted_at ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $user->deleted_at ? 'Deactivated' : ($user->is_active ? 'Active' : 'Inactive') }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-brand-600 text-xs hover:underline">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-6 py-8 text-center text-gray-400">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 md:px-6 py-3 md:py-4 border-t">{{ $users->links() }}</div>
</div>
@endsection
