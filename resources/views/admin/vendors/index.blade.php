@extends('layouts.app')
@section('title', 'Vendors')
@section('content')
<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <h2 class="text-lg md:text-xl font-bold text-gray-700">Vendors</h2>
    <a href="{{ route('admin.vendors.create') }}" class="text-white px-4 py-2 rounded text-sm font-medium btn-touch" style="background:#0056B3;">+ New vendor</a>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm" data-mobile="cards">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Code</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Contact</th>
                <th class="px-4 py-3 text-left">Phone</th>
                <th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($vendors as $v)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $v->vendor_code ?: '—' }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $v->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $v->contact_person ?: '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $v->phone ?: '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $v->email ?: '—' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $v->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $v->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.vendors.edit', $v) }}" class="text-brand-600 text-xs hover:underline">Edit</a>
                    <form method="POST" action="{{ route('admin.vendors.destroy', $v) }}" class="inline ml-3"
                          onsubmit="return confirm('Delete vendor &quot;{{ $v->name }}&quot;? Past tickets keep this vendor reference.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 text-xs hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">No vendors yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 md:px-6 py-3 md:py-4 border-t">{{ $vendors->links() }}</div>
</div>
@endsection
