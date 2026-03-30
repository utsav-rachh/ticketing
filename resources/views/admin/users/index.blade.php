@extends('layouts.app')
@section('title', 'Manage Users')
@section('content')
<h2 class="text-xl font-bold text-gray-700 mb-4">Users</h2>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Name</th>
                <th class="px-6 py-3 text-left">Email</th>
                <th class="px-6 py-3 text-left">Role</th>
                <th class="px-6 py-3 text-left">Department</th>
                <th class="px-6 py-3 text-left">Reports To</th>
                <th class="px-6 py-3 text-left">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50 {{ $user->deleted_at ? 'opacity-50' : '' }}">
                <td class="px-6 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                <td class="px-6 py-3 text-gray-500">{{ $user->email }}</td>
                <td class="px-6 py-3 capitalize text-xs"><span class="px-2 py-0.5 bg-brand-100 text-brand-700 rounded-full">{{ str_replace('_',' ',$user->role) }}</span></td>
                <td class="px-6 py-3 text-gray-500">{{ $user->department ?? '—' }}</td>
                <td class="px-6 py-3 text-gray-500">{{ $user->supervisor->name ?? '—' }}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $user->is_active && !$user->deleted_at ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $user->deleted_at ? 'Deactivated' : ($user->is_active ? 'Active' : 'Inactive') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $users->links() }}</div>
</div>
@endsection
