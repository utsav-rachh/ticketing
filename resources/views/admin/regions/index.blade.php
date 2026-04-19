@extends('layouts.app')
@section('title', 'Regions')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-gray-700">Regions</h2>
    <a href="{{ route('admin.regions.create') }}" class="text-white px-4 py-2 rounded text-sm font-medium" style="background:#0056B3;">+ New region</a>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Code</th>
                <th class="px-4 py-3 text-left">Branches</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($regions as $r)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $r->name }}</td>
                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $r->code }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $r->branches_count }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $r->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $r->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.regions.edit', $r) }}" class="text-brand-600 text-xs hover:underline">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No regions yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
