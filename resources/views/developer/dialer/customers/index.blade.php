@extends('layouts.developer')
@section('title', 'Dialer · Customers')
@section('content')

@include('developer.dialer._subnav')

<div class="flex items-start justify-between flex-wrap gap-3 mb-5">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Customers</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $customers->total() }} in the dialer database. Deduplicated on phone number.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('developer.dialer.customers.import') }}" class="text-sm px-3 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">CSV import</a>
        <button type="button" onclick="document.getElementById('addCustomer').classList.toggle('hidden')" class="text-sm px-3 py-2 rounded bg-brand-500 hover:bg-brand-600 text-white">+ Add customer</button>
    </div>
</div>

<div id="addCustomer" class="hidden mb-6 bg-white border border-gray-200 rounded-lg shadow-sm p-5">
    <form method="POST" action="{{ route('developer.dialer.customers.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs text-gray-500 mb-1">Name *</label>
            <input name="name" required value="{{ old('name') }}" class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Phone *</label>
            <input name="phone" required value="{{ old('phone') }}" placeholder="10-digit" class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm placeholder-gray-400">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Company</label>
            <input name="company" value="{{ old('company') }}" class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm">
        </div>
        <div>
            <button class="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium rounded px-3 py-2">Save</button>
        </div>
        <div class="md:col-span-5">
            <label class="block text-xs text-gray-500 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm">{{ old('notes') }}</textarea>
        </div>
    </form>
</div>

<form method="GET" class="mb-4">
    <input name="q" value="{{ $q }}" placeholder="Search name, phone, company, email…"
           class="w-full md:w-96 bg-white border border-gray-300 rounded px-3 py-2 text-sm placeholder-gray-400">
</form>

<div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-xs uppercase tracking-wide text-gray-400 border-b border-gray-200 bg-gray-50">
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Phone</th>
                <th class="px-4 py-2">Company</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Calls</th>
                <th class="px-4 py-2">Source</th>
                <th class="px-4 py-2">Added</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($customers as $c)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 font-medium text-gray-800">{{ $c->name }}</td>
                <td class="px-4 py-2 text-gray-600">{{ $c->phone }}</td>
                <td class="px-4 py-2 text-gray-600">{{ $c->company ?: '—' }}</td>
                <td class="px-4 py-2 text-gray-600">{{ $c->email ?: '—' }}</td>
                <td class="px-4 py-2 text-gray-400">{{ $c->tickets()->count() }}</td>
                <td class="px-4 py-2 text-gray-400">{{ $c->imported_from ?: '—' }}</td>
                <td class="px-4 py-2 text-gray-400">{{ $c->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No customers{{ $q ? ' match “'.$q.'”' : ' yet' }}.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $customers->links() }}</div>
@endsection
